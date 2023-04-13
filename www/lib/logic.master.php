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
                $action = $result;//." ".$action;
            } else {
                $door = find_door_for_input_device("reader_".$input, $controller->id);
                $action = $door->name. ": Access refused";
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
            if($controller->id == 1){
                checkAndHandleSensorLocal($gpio, $inputName, $alarmId, $controller);
            } else {
                checkAndHandleSensor($gpio, $inputName, $alarmId, $controller);
            }
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

/*
*   Handle sensor input 
*   includes delayed operations and recheck sensor input, will only work on match4
*
*   $gpio : sensor port number 
*   $inputName : sensor_1/_2
*   $alarmId : id of the alarm in db
*   $controller : controller object
*
*   TODO* checkAndHandleSensor has the same code as checkAndHandleSensorLocal, 
*   but I was unable to factor out the client request 
*
*   Used by inputListener 
*/
function checkAndHandleSensor($gpio, $inputName, $alarmId, $controller) {
    $doorSensorTriggerTime =find_setting_by_name("alarm");
    mylog("handleSensor on slave ".$controller->name.":".$inputName." triggerTime=".$doorSensorTriggerTime);

    //wait for the given trigger time, than check again
    $startAlarm = \React\EventLoop\Loop::addTimer($doorSensorTriggerTime, function () use ($gpio, $inputName, $alarmId, $controller) {
        mylog("recheck handleSensor ".$gpio);
        //--TODO*
        $client = new PhpCoap\Client\Client();
        $url = "coap://".$controller->ip."/value_".$gpio;
        mylog("checkSensor:".$url);
        $client->get($url, function( $data ) use ($gpio, $inputName, $alarmId, $controller) {
            mylog("checkSensor return=".$data);
            //--
            if( $data == '"1"' ) {
                setAlarm($controller, $alarmId, 1);
                saveReport("Unkown", "Alarm on ".$controller->name." from ".$inputName);

                //check if the door is closed, to turn of the alarm
                $stopAlarm = \React\EventLoop\Loop::addPeriodicTimer(0.5, function ($stopAlarm) use ($gpio, $inputName, $alarmId, $controller) {
                    //--TODO*
                    $client = new PhpCoap\Client\Client();
                    $url = "coap://".$controller->ip."/value_".$gpio;
                    mylog("checkSensor:".$url);
                    $client->get($url, function( $data ) use ($gpio, $inputName, $alarmId, $controller, $stopAlarm) {
                        mylog("checkSensor return=".$data);
                        //--
                        if( $data == '"0"' ) {
                            setAlarm($controller, $alarmId, 0);
                            saveReport("Unkown", "Alarm stopped for ".$controller->name." from ". $inputName);
                            \React\EventLoop\Loop::cancelTimer($stopAlarm);
                        }
                    });
                });
            } 
        });
    }); 

    //recheck if sensor is still active, if not cancel the timers
    $recheckAlarm = \React\EventLoop\Loop::addPeriodicTimer(0.5, function ($recheckAlarm) use ($startAlarm, $gpio, $controller) {
        $client = new PhpCoap\Client\Client();
        $url = "coap://".$controller->ip."/value_".$gpio;
        mylog("checkSensor:".$url);
        $client->get($url, function( $data ) use ($gpio, $startAlarm, $recheckAlarm) {
            mylog("checkSensor return=".$data);
            if( $data == '"0"' ) {
                mylog("recheck: false alarm handleSensor ".$gpio);
                \React\EventLoop\Loop::cancelTimer($startAlarm);
                \React\EventLoop\Loop::cancelTimer($recheckAlarm);
            }
        });
    });   
}

function checkAndHandleSensorLocal($gpio, $inputName, $alarmId, $controller) {
    $doorSensorTriggerTime =find_setting_by_name("alarm");
    mylog("handleSensor on master ".$controller->name.":".$inputName." triggerTime=".$doorSensorTriggerTime);

    //wait for the given trigger time, activate alarm if sensor is still active.
    $startAlarm = \React\EventLoop\Loop::addTimer($doorSensorTriggerTime, function () use ($gpio, $inputName, $alarmId, $controller) {
        mylog("recheck handleSensor ".$gpio);

        if(checkValue($gpio, $controller) == 1) {
            setAlarm($controller, $alarmId, 1);
            saveReport("Unkown", "Alarm for ".$controller->name." from ".$inputName);

            //check if the door is closed, to turn of the alarm
            $stopAlarm = \React\EventLoop\Loop::addPeriodicTimer(0.5, function ($stopAlarm) use ($gpio, $inputName, $alarmId, $controller) {

                if(checkValue($gpio, $controller) == 0) {
                    setAlarm($controller, $alarmId, 0);
                    saveReport("Unkown", "Alarm stopped for ".$controller->name." from ". $inputName);
                    \React\EventLoop\Loop::cancelTimer($stopAlarm);
                }
            });
        } 
    });

    //recheck if sensor is still active, if not cancel the timers
    $recheckAlarm = \React\EventLoop\Loop::addPeriodicTimer(0.5, function ($recheckAlarm) use ($startAlarm, $gpio, $controller) {
        if(checkValue($gpio, $controller) == 0) {
            mylog("recheck: false alarm handleSensor ".$gpio);
            \React\EventLoop\Loop::cancelTimer($startAlarm);
            \React\EventLoop\Loop::cancelTimer($recheckAlarm);
        }
    });
}

/*
* Set Alarm
* 
*   $controller : Controller object
*   $alarmId : gpio value of the sensor
*   $value : 1=on, 0=off
*
* Used by match_listener
*/
function setAlarm($controller, $alarmId, $value) {
    //resolve the gpio of an alarm
    $gid = ($alarmId == 1) ? GVAR::$GPIO_ALARM1 :GVAR::$GPIO_ALARM2;
    mylog("setAlarm cid=".$controller->id." gid=".$value." value=".$value);
    if( $controller->id == 1 ) {
        setGPIO($gid, $value);
    } else {
        //setGPIO($gid, $value);
        //TODO +2 is te vies
        $uri = "output_".($alarmId + 2)."_".$value;
        mylog($uri);
        return apiCall($controller->ip, $uri);
    }
}


/*
* Check sensor value 
* 
*   $gpio : gpio value of the sensor
*   $controller : Controller object
*   returns current value
*
* Used by match_listener
*/
function checkValue($gpio, $controller) {
    mylog("checkValue cid=".$controller->id." gpio=".$gpio);
    if( $controller->id == 1 ) {
        return getGPIO($gpio);
    } else { //is never used
        //TODO* this is not working need to wrap it in a defered async closure thingie 
        // and this is why we have checkAndHandleSensorLocal and checkAndHandleSensor
        $uri = "value_".$gpio;
        mylog($uri);
        return apiCall($controller->ip, $uri);
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
    //Check if user is active
    if(! is_user_active($user) ) {
        return "User is inactive";
    }

    //Check maximum visits for user 
    if(!empty($user->max_visits) && $user->visit_count >= $user->max_visits) {
        return "Maximum visits reached:  visits = ".$user->max_visits;
    }
    //Check start/end date for user 
    $now = new DateTime();
    mylog($now);
    $startDate = DateTime::createFromFormat(getDateTimeFormat(), $user->start_date, new DateTimeZone(getTimezone() ) );
    mylog($startDate);
    // $diff = $now->diff($startDate);
    // mylog("diff=".$diff->h."_".$now->diff($startDate)->i);
    if($now < $startDate) {
        return "Access has not started: Start date = ".$user->start_date;
    }

    $endDate = DateTime::createFromFormat(getDateTimeFormat(), $user->end_date, new DateTimeZone( getTimezone() ) );
    mylog($endDate);
    if ($endDate && $now > $endDate) {
        return "Access has expired: End date = ".$user->end_date;
    }

    //APB, if the user is back within APB time, deny access
    $lastSeen = new DateTime($user->last_seen, new DateTimeZone('UTC')); //always calc with UTC
    $diff =  $now->getTimestamp() - $lastSeen->getTimestamp();
    $apb = find_setting_by_name('apb'); //apb is defined in seconds
    mylog("lastseen=".$lastSeen->format("c")." now=".$now->format("c")." diff=".$diff." seconds");
    if($diff < $apb && $diff > 0) {
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
    
    //update attendance list, keeping score of who is in or out.
    if(useLedgerMode()) {
        update_ledger($user, $readerId);
    }

    //update last_seen en visit_count
    update_user_statistics($user);

    //open the door 
    $msg = openDoor($door, $controller);
    $msg = $door->name;//."@".$controller->name;
    return $msg;    
}

/*
*   Check if a door is Scheduled to open
*       returns true if it is open and false for closed
*   $doorId : id in the db
*   Used by match_listener and webinterface
*/
function checkDoorSchedule($door) {
    $tz = find_timezone_by_id($door->timezone_id);
    //mylog($door);
    //mylog($tz);
    mylog("checkDoorSchedule door=".$door->id." tz=".$door->timezone_id);
    if($door->timezone_id) {
        //$now = new DateTime('now', new DateTimeZone('Europe/Amsterdam'));
        $now = new DateTime('now', new DateTimeZone(getTimezone()));
        mylog($now);
        //check if it is the right day of the week
        $weekday = $now->format('w');//0 (for Sunday) through 6 (for Saturday) 
        $weekdays = explode(",",$tz->weekdays);
        if(in_array($weekday, $weekdays)) {
            mylog("checkDoorSchedule begin/end");
            //check if it is the right time
            $begin = new DateTime($tz->start, new DateTimeZone(getTimezone()));
            $end = new DateTime($tz->end, new DateTimeZone(getTimezone()));
            mylog($begin);
            mylog($end);
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
* Change Door State - Open or Close a door
* 
*   $door : Door object
*   $controller : Controller object
*   $state : 0 or 1 
*   returns  
*
* Used by webinterface
*/
function changeOutputState($outputEnum, $controller, $door, $state) {
    if( $controller->id == 1 ) {
        //call method on master, is quicker and more reliable
        //and nesting coap-client calls is not working currently
        $gid = getOutputGPIO($outputEnum);
        setGPIO($gid, $state);
        //TODO actual return state
        return true;
    } else {
        $url = "coap://".$controller->ip."/output_".$outputEnum."_".$state;
        mylog("coapCall:".$url);
        //request
        $client = new PhpCoap\Client\Client();
        $client->get($url, function( $msg ) use ($controller, $door, $state){
            mylog("changeOutputState apiCall return=".$msg);
            if($msg == -1) {
                saveReport("WebAdmin", $controller->name." Controller does not respond");
            } else {
                saveReport("WebAdmin", "Switch ".$door->name." ".($state?"open":"closed")." on ".$controller->name);
            }
        });
    }
}

/*
* Operate a door 
*   $door : Door object
*   $open : 1=open, 0=close
*   returns true if state was changed
*
* Only used by cron / scheduler 
* and remove_timezone in the gui
*/

function operateDoor($door, $open) {
    $deferred = new React\Promise\Deferred();

    if( $door->controller_id == 1) { //Master = 1
        $gid = getOutputGPIO($door->id);

        $currentValue = getGPIO($gid);
        mylog("openLock ".$currentValue."=".$open);

        //check if lock state has changed
        if($currentValue != $open) {
            $action = $door->name." is ".(($open == 1)?"opened":"closed");
            setGPIO($gid, $open);
            mylog("CHANGED:".$action);
            saveReport("Scheduled", $action);
            $deferred->resolve($action);
        }
        $deferred->resolve("NO CHANGE on ".$door->name);

    } else { //Slave
        //get slave data, to get ip address
        $controller = find_controller_by_id($door->controller_id );
        mylog($controller);

        $gid = getOutputGPIO($door->enum);
        $url = "coap://".$controller->ip."/status_".$gid;
        mylog("checkDoor:".$url);

        //request coap-client -m get coap://$slave/status_1
        $client = new PhpCoap\Client\Client();
        $client->get($url, function( $data ) use ($gid, $open, $door, $controller, $deferred){
            mylog("checkDoor return=".$data);

            //check if request was successfull
            if($data == -1) {
                $action = $controller->name." Controller does not respond";
                mylog($action);
                saveReport("Scheduled", $action);
                $deferred->resolve($action);

                //Stop and return the promise
                return $deferred->promise();
            }

            //check if lock state has changed
            $currentValue = json_decode($data)[0]->{"$gid"}; //[{"68":"0"}]
            mylog("currentValue return=".$currentValue);
            if($currentValue != $open) {

                //change lock state
                $url = "coap://".$controller->ip."/output_".$door->enum."_".$open;
                mylog("openDoor:".$url);
                //request coap-client -m get coap://$slave/output_1_1
                $client = new PhpCoap\Client\Client();
                $client->get($url, function( $data ) use ($open, $door, $deferred) {
                    mylog("openDoor return=".$data);
                    $action = $door->name." is ".(($open == 1)?"opened":"closed");
                    saveReport("Scheduled", $action);
                    $deferred->resolve($action);
                });                
            } else {
                $deferred->resolve("NO CHANGE on ".$door->name);
            }
        }); 
    }    
    // Return the promise
    return $deferred->promise();
}


/*
*   Get available controllers to command for the master
*   -
*/
function available_controllers() {
    $result = mdnsBrowse("_maasland._udp");
    mylog($result);

    //Remove controllers already used
    $controllers = find_controller_ips();
    mylog($controllers);

    //Remove master controller
    $masterIp = mdnsBrowse("_master._sub._maasland._udp")[0][7];
    $result = array_values(array_filter($result, function($v) use($masterIp, $controllers){ 
        mylog($v[7] ."-x-". $masterIp);
        return !in_array($v[7], $controllers) && $v[7] != $masterIp;
        //return $v[7] != $masterIp; 
    }));
    mylog($result);
    // if(empty($result)) {
    //     return json( [["","","","","","","g","h"]] );
    // }   
    return json($result);
}

/*
*   Special mode, where users are tracked if they are in or out
*   - reader1=in 
*   - reader2=out
*/
function useLedgerMode() {
    $ledger=find_setting_by_name("ledger");
    if(!empty($ledger) && $ledger=="qwerty") {
        return true;
    }
    return false;
}


