<?php
require_once '/maasland_app/www/lib/logic.slave.php';

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

function handleInput($from, $input, $keycode) {
    $controller = resolveController($from);
    if(empty($controller)) {
        return "unkown controller";
    }
    mylog("handleInput Controller=".$controller->name." input=".$input." keycode=".$keycode);

    $actor = "somebody";
    $result = "nothing";
    switch ($input) {
        case 1:
        case 2:
            $action = "Reader ".$input." key:".$keycode;
            //get User for the key
            $user = find_user_by_keycode($keycode);
            if($user) {
                $actor = $user->name;
                $result = handleUserAccess($user, $input, $controller);
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
            $door = find_door_for_input_device($inputName, $controller->id);
            $action = $inputName.":".$door->name;
            $result = $action;
            //TODO checkAndHandleSensor
            break;
        default:
            $action = "illegal";
            break;
    }    
    //save report
    saveReport($actor, $action, keyToHex($keycode));
    return array(
        "actor" =>$actor, 
        "controller" => $controller->name, 
        //"controller" => $controller, 
        "result" => $result
    );
}

function checkAndHandleSensor($gpio, $id, $controller) {
    if(getGPIO($gpio) == 1) {
        $name = "Sensor ".$id;
        mylog("handleSensor ".$name);
        $pollTime = 1; //interval for checking if the door is closed again.
        $doorSensorTriggerTime =find_setting_by_name("alarm");

        //wait for the given trigger time, than check again
        sleep($doorSensorTriggerTime);
        if(getGPIO($gpio) == 1) {
            //find what alarm to open
            $alarm = find_alarm_for_sensor_id($id,$controller->id);
            $gid = ($alarm == 1) ? GVAR::$GPIO_ALARM1 :GVAR::$GPIO_ALARM1;
            setGPIO($gid, 1);
            //save report
            saveReport("Unkown", "Alarm ".$door->name." from ". $name);

            //check if the door is closed, to turn of the alarm
            while(true) {
                if(getGPIO($gpio) == 0) {
                    setGPIO($gid, 0);
                    saveReport("Unkown", "Alarm stopped for ".$door->name." from ". $name);
                    break;
                }
                sleep($pollTime);
            }
        }
    }
}

/*
*   Handle user access by reader
*   $user : object 
*   $readerId : id in the db
*   $controller : controller object
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
    $lastSeen = new DateTime($user->last_seen, new DateTimeZone('Europe/Amsterdam'));
    //TODO fix timezone mess -getOffset is a hack?
    $diff =  $now->getTimestamp() - $now->getOffset() - $lastSeen->getTimestamp();
    $apb = find_setting_by_name('apb'); //apb is defined in seconds
    mylog("lastseen=".$lastSeen->format("c")." now=".$now->format("c")." diff=".$diff." seconds");
    if($diff < $apb) {
        return "APB restriction: no access within ".$diff." seconds, must be longer than ".$apb." seconds";
    }

    //Determine what door to open
    $door = find_door_for_input_device("reader_".$readerId, $controller->id);

    //Don't open the door if it is scheduled to be open
    if(checkDoorSchedule($door)) {
        return "Door is already scheduled to be open: ".$door->name;
    }

    //check if the group/user has access for this door
    $tz = find_timezone_by_group_id($user->group_id, $door->id);
    mylog("tz=".json_encode($tz));
    if(empty($tz)) {
        return "Door can not be used. No timezone assigned to this door";
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




/*** Output functionality ***/


/*
*   Check if a door is in a Schedule
*   $doorId : id in the db
*   Used by match_listener and webinterface
*/
function checkDoorSchedule($door) {
    $tz = find_timezone_by_id($door->timezone_id);
    // mylog("checkDoorSchedule door=".$door->id." tz=".$door->timezone_id);
    // if($door->timezone_id) {
        $now = new DateTime();
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
    //}
    return false;
}

/*
* Open a door
*   $door : Door object
*   $controller : Controller object
*   returns  
*
* Used by match_listener and webinterface
*/
function openDoor($door, $controller) {
    $duration=find_setting_by_name("door_open");
    $soundBuzzer=find_setting_by_name("sound_buzzer");
    mylog(json_encode($door));
    mylog(json_encode($controller));
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

    if( $controller->id === 1 ) {
        //call method on master, is quicker and more reliable
        //and nesting coap-client calls is not working currently
        $msg = activateOutput($door->id, $duration, $gpios);
    } else {
        $cmd = "coap-client -m get coap://".$controller->ip."/activate/".$door->enum."/".$duration."/".implode("-",$gpios);
        mylog($cmd);
        $msg = shell_exec($cmd);
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
    if( $door->controller_id === 1) { //Master = 1
        $gid = getDoorGPIO($door->id);

        $currentValue = getGPIO($gid);
        //mylog("openLock ".$currentValue."=".$open."\n");

        //check if lock state has changed
        if($currentValue != $open) {
            //mylog("STATE CHANGED=".$open);
            setGPIO($gid, $open);
            return true;
        }
        return false;
    } else {
        $controller = find_controller_by_id($door->controller_id );

        $duration =1;
        $gpios = array();
        $cmd = "coap-client -m get coap://".$controller->ip."/operate/".$door->id;
        $msg = shell_exec($cmd);
    }
    return $msg;
}





