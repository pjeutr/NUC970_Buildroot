<?php
/*
*   Masterside methods.
*   - has NO knowledge which GPIO's belong to what
*   - has NO functions that call the local hardware
*   - has knowledge about content in the database
inputReceived -> (coap) -> handleInput -> handleUserAccess -> openDoor -> (coap) -> activateOutput

handleInput (resolves user information)
handleUserAccess (resolves logic configuration)
openDoor (aggregate hardware information and translate to gpio numbers)
*/

function resolveController($ip) {
    mylog("resolveController ip=".$ip);
    return find_controller_by_ip($ip);
}

//incomming coap input
    // button_1 = 170 
    // button_2 = 169 
    // sensor_1 = 168 
    // sensor_2 = 45
    // writer > coap://master/input/1/1234 >  handleUserAccess
    // getGPIO < checkOutput < coap://slave/status/66-2 < checkOutput
    // setGPIO < operateOutput < coap://slave/output/2/1/2-10 < handleUserAcess
    // setGPIO < activateOutput < coap://slave/activate/2/5/2-10 < checkAndHandleInput
    // door_1 = 68
    // door_2 = 66
    // alarm_1 = 65
    // alarm_2 = 66
/*
*   Handle incomming input 
*   resolve user information
*
*   $user : object 
*   $readerId : id in the db
*   $controller : controller object
*
*   return json
*
*   Used by inputListener 
*/
function handleInput($from, $input, $keycode) {
    $controller = resolveController($from);
    mylog(json_encode($controller));
    if(empty($controller)) {
        saveReport($from, $input, "unkown controller");
        return "unkown controller";
    }
    mylog("handleInput Controller=".$controller->name." input=".$input." keycode=".$keycode);

    $actor = "somebody";
    $result = "nothing";
    switch ($input) {
        case 1:
        case 2:
            $action = "Reader ".$input;
            //get User for the key
            $user = find_user_by_keycode($keycode);
            if($user) {
                $actor = $user->name;
                $result = handleUserAccess($user, $input, $controller);
                $action = $action." ".$result;
            } 
            break;
        case 3:
        case 4:
            $inputName = ($input == 3) ? "button_1":"button_2";
            $door = find_door_for_input_device($inputName, $controller->id);
            $action = $inputName.":".$door->name;
            $result = openDoor($door, $controller);
            break;
        case 5:
        case 6:
            $inputName = ($input == 5) ? "sensor_1":"sensor_2";
            $gpio = ($input == 5) ? GVAR::$GPIO_DOORSTATUS1:GVAR::$GPIO_DOORSTATUS2;
            $alarmId = find_alarm_for_sensor_id($inputName, $controller->id);
            $action = $inputName.":".$controller->name;
            mylog($gpio."_".$inputName." alarmId=".$alarmId);
            checkAndHandleSensor($gpio, $inputName, $alarmId, $controller);
            $result = $action;
            break;
        default:
            $action = "illegal";
            break;
    }    
    //save report
    saveReport($actor, $action, keyToHex($keycode));
    mylog("handleInput result:".$result);
    return array(
        "actor" =>$actor, 
        "controller" => $controller->name, 
        //"controller" => $controller, 
        "result" => $result
    );
}

function checkAndHandleSensor($gpio, $inputName, $alarmId, $controller) {
    mylog($gpio."=checkAndHandleSensor=".getGPIO($gpio));
    if(getGPIO($gpio) == 1) {
        $doorSensorTriggerTime =find_setting_by_name("alarm");
        mylog("handleSensor ".$inputName." triggerTime=".$doorSensorTriggerTime);

        //wait for the given trigger time, than check again
        $loop = React\EventLoop\Loop::get();
        $loop->addTimer($doorSensorTriggerTime, function () use ($gpio, $inputName, $alarmId, $controller, $loop) {
            mylog("recheck handleSensor ".$gpio);
            //TODO poll more to check if open?
            if(getGPIO($gpio) == 1) {
                $gid = ($alarmId == 1) ? GVAR::$GPIO_ALARM1 :GVAR::$GPIO_ALARM2;
                setGPIO($gid, 1);
                //save report
                saveReport("Unkown", "Alarm on ".$controller->name." from ".$inputName);

                //check if the door is closed, to turn of the alarm
                while(true) {
                    if(getGPIO($gpio) == 0) {
                        setGPIO($gid, 0);
                        saveReport("Unkown", "Alarm stopped for ".$controller->name." from ". $inputName);
                        break;
                    }
                    sleep(1);//interval for checking if the door is closed again.
                }
            } else {
                //TODO remove 
                mylog("FALSE alarm handleSensor ".$gpio);
            }

        });


    }
}

/*
*   Handle user access by reader
*   resolves logic configuration
*
*   $user : object 
*   $readerId : id in the db
*   $controller : controller object
*
*   Used by match_listener 
*/
function handleUserAccess($user, $readerId, $controller) {
    mylog("handleUserAccess user".$user->name." readerId=".$readerId);
    //Check maximum visits for user 
    if(!empty($user->max_visits) && $user->visit_count > $user->max_visits) {
        return "Maximum visits reached:  visits = ".$user->max_visits;
    }
    //Check start/end date for user 
    $now = new DateTime();
    $startDate = new DateTime($user->start_date);
    $endDate = new DateTime($user->end_date);
    if ($now < $startDate || $now > $endDate) {
        return "Access has expired: Start date = ".$user->start_date." End date = ".$user->end_date;
    }

    //APB, if the user is back within APB time, deny access
    $lastSeen = new DateTime($user->last_seen, new DateTimeZone('UTC')); //always calc with UTC
    $diff =  $now->getTimestamp() - $lastSeen->getTimestamp();
    $apb = find_setting_by_name('apb'); //apb is defined in seconds
    mylog("lastseen=".$lastSeen->format("c")." now=".$now->format("c")." diff=".$diff." seconds");
    if($diff < $apb && $diff !== 0) {
        return "APB restriction: no access within ".$diff." seconds, must be longer than ".$apb." seconds";
    }

    //Determine what door to open
    $door = find_door_for_input_device("reader_".$readerId, $controller->id);

    //Don't open the door if it is scheduled to be open
    if(checkDoorSchedule($door)) {
        return "Door is already scheduled to be open: ".$door->name;
    }

    //TODO mag user deze deur wel open maken?

    //check if the group/user has access for this door
    $tz = find_timezone_by_group_id($user->group_id, $door->id);
    mylog("tz=".json_encode($tz));
    if(empty($tz)) {
        return "Door can not be used. No timezone assigned to this door for this group.";
    }
    mylog("group=".$user->group_id." door=".$door->id."=".$door->name);
    mylog("name=".$tz->name." start=".$tz->start." end=".$tz->end);

    //check if it is the right day of the week
    $weekday = $now->format('w');//0 (for Sunday) through 6 (for Saturday) 
    $weekdays = explode(",",$tz->weekdays);
    mylog("weekday=".$weekday."=".$tz->weekdays);
    if(! in_array($weekday, $weekdays)) {
        return "Day of the week restriction: ".$weekday." is not in ".$tz->weekdays." ";
    }

    //check if it is the right time
    $begin = new DateTime($tz->start);
    $end = new DateTime($tz->end);
    if ($now < $begin || $now > $end) {
        return "Time of the day restriction: ".$now->format('H:m')." is not between ".$tz->start." and ".$tz->end;
    }
    
    //update last_seen en visit_count
    update_user_statistics($user);

    //open the door 
    $msg = openDoor($door, $controller);
    
    return $msg;    
}

/*
*   Check if a door is in a Schedule
*   $doorId : id in the db
*   Used by match_listener and webinterface
*/
function checkDoorSchedule($door) {
    $tz = find_timezone_by_id($door->timezone_id);
    //mylog($door);
    //mylog($tz);
    mylog("checkDoorSchedule door=".$door->id." tz=".$door->timezone_id);
    if($door->timezone_id) {
        $now = new DateTime();
        mylog($now);
        //check if it is the right day of the week
        $weekday = $now->format('w');//0 (for Sunday) through 6 (for Saturday) 
        $weekdays = explode(",",$tz->weekdays);
        if(in_array($weekday, $weekdays)) {
            mylog("checkDoorSchedule begin/end");
            //check if it is the right time
            $begin = new DateTime($tz->start);
            $end = new DateTime($tz->end);
            if ($now >= $begin && $now <= $end) {
                return true;
            }
        }
    }
    return false;
}

/*
* Open a door 
* aggregate hardware information and translate to gpio numbers
*   $door : Door object
*   $controller : Controller object
*   returns  
*
* Used by match_listener and webinterface
*/
function openDoor($door, $controller) {
    $duration=find_setting_by_name("door_open");
    $soundBuzzer=find_setting_by_name("sound_buzzer");
    mylog("Door=".json_encode($door));
    mylog("Cont=".json_encode($controller));
    mylog("Open Door ".$door->id." cid=".$controller->id." duration=".$duration." sound_buzzer=".$soundBuzzer);

    $gpios = array();
    //aggegrate gpios to switch on/off
    if($soundBuzzer) $gpios[] = GVAR::$BUZZER_PIN;

    //add the right wiegand reader leds for a door
    $door1 = find_door_for_reader_id(1,$controller->id);
    mylog("Reader1 does door=".$door1->id." Now doing door=".$door->enum);
    if($door1->id === $door->enum){
        $gpios[] = GVAR::$RD1_GLED_PIN;
    }
    $door2 = find_door_for_reader_id(2,$controller->id);
    mylog("Reader2 does door=".$door2->id." Now doing door=".$door->enum);
    if($door2->id ===  $door->enum){
        $gpios[] = GVAR::$RD2_GLED_PIN;
    }
    //mylog("extra gpios=".json_encode($gpios));
    mylog("extra gpios=".json_encode($gpios));

    if( $controller->id == 1 ) {
        //call method on master, is quicker and more reliable
        //and nesting coap-client calls is not working currently
        $msg = activateOutput($door->id, $duration, $gpios);
    } else {
        $uri = "activate/".$door->enum."/".$duration."/".implode("-",$gpios);
        $msg = apiCall($controller->ip, $uri);
    }
    return $msg;
}

/*
* Operate a door 
*   $door : Door object
*   $open : 1=open, 0=close
*   returns true if state was changed
*
* Only used by cron / scheduler
*/

function operateDoor($door, $open) {
    mylog("door= ".json_encode($door));

    //if( checkIfMaster() ) {
    if( $door->controller_id == 1) { //Master = 1
        $gid = getDoorGPIO($door->id);

        $currentValue = getGPIO($gid);
        mylog("openLock ".$currentValue."=".$open."\n");

        //check if lock state has changed
        if($currentValue != $open) {
            //mylog("STATE CHANGED=".$open);
            setGPIO($gid, $open);
            return true;
        }
        return false;
    } else {
        $controller = find_controller_by_id($door->controller_id );
        mylog($controller);

        $gpios = array();
        //TODO could also use leds? see openDoor
        // $gpios[] = GVAR::$RD1_GLED_PIN;
        // $gpios[] = GVAR::$RD2_GLED_PIN;

/*
        $cmd = "coap-client -m get coap://".$controller->ip."/status/".getDoorGPIO($door->enum);
        $msg = shell_exec($cmd);
        mylog("----------");
        mylog(json_decode($msg)[0]);
        mylog("----------");
        //TODO find a way nice way to pick the value
        $currentValue = 1;
        mylog("openLock ".$currentValue."=".$open."\n");

        //TODO check if already open? anticipate tz change during
        if($currentValue != $open) { */
        if(true) {
            //mylog("STATE CHANGED=".$open);

            $uri = "output/".$door->enum."/".$open."/".implode("-",$gpios);
            $msg = apiCall($controller->ip, $uri);
            mylog($msg);
            return true;
        }
        
    }
    return false;
}





