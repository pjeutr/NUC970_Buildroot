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
*   $from : ip adress of the controller
*   $input : 1 or 2 = wiegand, 3 or 4 = button, 5 or 6 = sensor
*   $keycode : keycode send by wiegand reader
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
            error_log("illegal Controller=".$controller->name." input=".$input." keycode=".$keycode);
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
    $now = new DateTime(); //now in UTC
    $nowLocal = new DateTime("now", new DateTimeZone( getTimezone() ) ); //now on local time, use only to compare with user set datetime in the gui/db
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

    //keep track when user was last in
    $lastSeen = new DateTime($user->last_seen, new DateTimeZone('UTC')); //always calc with UTC
    mylog("lastseen=".$lastSeen->format("c"));

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
        return "$door->name can not be used. No timezone assigned to this door for this group.";
    }
    mylog("group=".$user->group_id." door=".$door->id."=".$door->name);
    mylog("name=".$tz->name." start=".$tz->start." end=".$tz->end);

    //Deny access on holiday
    if($tz->id != 1) { //access groups with timezone 1 (24/7). Have always access 
        if($holiday = checkHoliday()) {
            return "Holiday restriction: ".$holiday->name." between ".$holiday->start_date. " and " .$holiday->end_date;
        }
    }

    //check if it is the right day of the week
    $weekday = $now->format('w');//0 (for Sunday) through 6 (for Saturday) 
    $weekdays = explode(",",$tz->weekdays);
    mylog("weekday=".$weekday."=".$tz->weekdays);
    if(! in_array($weekday, $weekdays)) {
        return "Day of the week restriction: ".$weekday." is not in ".$tz->weekdays." ";
    }

    //check if it is the right time
    $begin = DateTime::createFromFormat(getTimeFormat(), $tz->start, new DateTimeZone(getTimezone() ) );
    $end = DateTime::createFromFormat(getTimeFormat(), $tz->end, new DateTimeZone(getTimezone() ) );

    // mylog($nowLocal);
    // mylog("begin-end");
    // mylog($begin);
    // mylog($end);

    if(! isBetween($begin, $end, $now)) {
        return "Time of the day restriction: ".$nowLocal->format('H:i')." is not between ".$tz->start. "-" .$tz->end;
    }

    //update attendance list, keeping score of who is in or out.
    update_ledger($user, $readerId);

    //APB, if user is already -> deny access, unless it's in a 24h group
    //check only for APB controllers on IN reader 1 
    mylog("apb check present=".$user->present." tz=".$tz->id);
    if($user->present == 1 && $tz->id != 1) {
        if($readerId == 1 && $controller->apb == 1) {
            return "APB restriction: user is already present";
        } else {
            //set user out
        }
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
            if(isBetween($begin, $end, $now)) {
                return true;
            }
        }
    }
    return false;
}

/*
*   Check if it's a holiday
*       returns true if it is
*   $doorId : id in the db
*   Used by match_listener and schedule
*/
function checkHoliday() {
    $now = new DateTime(); //now in UTC
    $holidays = find_holidays();
    foreach ($holidays as $holiday) {
        $begin = DateTime::createFromFormat(getDateTimeFormat(), $holiday->start_date, new DateTimeZone(getTimezone() ) );
        $end = DateTime::createFromFormat(getDateTimeFormat(), $holiday->end_date, new DateTimeZone(getTimezone() ) );
        mylog("check holiday=".$holiday->name." begin=".$begin->format(getDateTimeFormat())." end=".$end->format(getDateTimeFormat())." now=".$now->format(getDateTimeFormat()));

        if($begin <= $now && $now <= $end) {
            return $holiday;
        }
    }
    return false;
}

/*
* Open a door  
*   $door : Door object
*   $controller : Controller object
*   opens door locally on master or bij apiCall on slave
*
* Used by match_listener and webinterface
*/
function openDoor($door, $controller) {
    $data = getDoorData($door, $controller->id);

    if( $controller->id == 1 ) {
        //call method on master, is quicker and more reliable
        $result = activateOutput($data->enum, $data->duration, $data->gpios);
        mylog("master activateOutput return=".json_encode($data));
    } else {
        //call api on slave
        $uri = "activate/".$data->enum."/".$data->duration."/".implode("-",$data->gpios);
        $result = apiCall($controller->ip, $uri);
        mylog("slave apiCall return=".json_encode($data));
    }
}

/*
* Get data to open a door 
* aggregate hardware information and translate to gpio numbers
*   $door : Door object
*   $controllerId : controller id in database
*   returns object with
*
* Used by inputListener, coapServer and webinterface
*/
function getDoorData($door, $controllerId) {
    $duration=find_setting_by_name("door_open");
    
    mylog("Door=".json_encode($door));
    mylog("Open Door ".$door->id." cid=".$controllerId." duration=".$duration);

    $gpios = array();
    //aggegrate gpios to switch on/off

    //add the right wiegand reader leds for a door
    $doorForReader1 = find_door_for_reader_id(1,$controllerId);
    mylog("Reader1 does door=".$doorForReader1->enum." with enum=".$door->enum);
    if($doorForReader1->enum === $door->enum){
        $gpios[] = GVAR::$RD1_GLED_PIN;
    }
    $doorForReader2 = find_door_for_reader_id(2,$controllerId);
    mylog("Reader2 does door=".$doorForReader2->enum." with enum=".$door->enum);
    if($doorForReader2->enum ===  $door->enum){
        $gpios[] = GVAR::$RD2_GLED_PIN;
    }
    //mylog("extra gpios=".json_encode($gpios));
    mylog("extra gpios=".json_encode($gpios));

    return (object) array(
        "enum" =>$door->enum, 
        "duration" => $duration, 
        "gpios" => $gpios, 
        "name" => $door->name
    );
}

/*
* Change Door State - Open or Close a door
* 
*   $door : Door object
*   $controller : Controller object
*   $state : 0 or 1 
*   returns state or -1 in case of an error
*
* Used by webinterface
*/
function changeOutputState($outputEnum, $controller, $door, $state) {
    $data = getDoorData($door, $controller->id);
    //TODO data is redundant, door of outputEnum kan weg
    if( $controller->id == 1 ) {
        //call method on master, is quicker and more reliable
        //and nesting coap-client calls is not working currently
        saveReport("WebAdmin", $door->name." ".($state?"open":"closed")." on ".$controller->name);
        $data = getDoorData($door, $controller->id);
        return operateOutput($door->enum, $state, $data->gpios);
    } else {
        $url = "coap://".$controller->ip."/output_".$outputEnum."_".$state."_".implode("-",$data->gpios);
        mylog("coapCall:".$url);
        //request
        $client = new PhpCoap\Client\Client();
        $client->get($url, function( $msg ) use ($controller, $door, $state){
            mylog("changeOutputState apiCall return=".$msg);
            if($msg == -1) {
                saveReport("WebAdmin", $controller->name." Controller does not respond");
                return -1;
            } else {
                saveReport("WebAdmin", $door->name." ".($state?"open":"closed")." on ".$controller->name);
                return $state;
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

            $data = getDoorData($door, $door->controller_id);
            operateOutput($door->enum, $open, $data->gpios);

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
                //saveReport("Scheduled", $action);
                $deferred->resolve($action);

                //Stop and return the promise
                return $deferred->promise();
            }

            //check if lock state has changed
            $currentValue = json_decode($data)[0]->{"$gid"}; //[{"68":"0"}]
            mylog("currentValue return=".$currentValue);
            if($currentValue != $open) {

                //change lock state
                $data = getDoorData($door, $controller->id);
                $url = "coap://".$controller->ip."/output_".$door->enum."_".$open."_".implode("-",$data->gpios);
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
*   Replicate config to the slaves
*   -
*/
function replicate_to_slaves() {
    $result = "";
    $path = "/maasland_app/www/db";
    //Remove old clone 
    $result .=  doExec("rm $path/clone.db", 
        "Remove old config");
    //Create new clone
    $result .=  doExec("sqlite3 $path/prod.db '.dump users groups doors rules controllers timezones settings' | sqlite3 $path/clone.db", 
        "Create new config");

    $controllers = find_controllers();
    //if(false){
    foreach ($controllers as $controller) {
        $result .= "<hr>SLAVE ip=" . $controller->ip."<br>";
        //Clean previous remarks
        $result .=  doExec("sqlite3 $path/clone.db 'UPDATE controllers SET remarks = null;'", 
            "Prepare config");
        //Mark current slave 
        $sql = "UPDATE controllers SET remarks = \"this_is_me\" WHERE ip = \"$controller->ip\"";
        $result .=  doExec("sqlite3 $path/clone.db '$sql';", 
            "Mark current slave");
        
        //REMARK regarding keys
        // /root/.ssh/id_rsa is used for communication between controllers
        // /etc/dropbear/dropbear_ecdsa_host_key is used for communication to github
        //ssh -i /root/.ssh/id_rsa root@192.168.178.41
        //scp doesn't work on busybox
        //$cmd = "scp clone.db -f /root/.ssh/id_rsa root@192.168.178.41:/maasland_app/www/db/"; 
        //StrictHostKeychecking doesn't work with dropbear, so we use dbclient

        //Copy db to slave
        $result .=  doExec("cat $path/clone.db | dbclient -y -i /root/.ssh/id_rsa root@$controller->ip 'cat > $path/remote.db'", 
            "Copy config to slave");
    }
    return $result;
}
function doExec($cmd, $name){
    mylogDebug($cmd);
    exec($cmd.' 2>&1',$output, $retval);
    mylogDebug($output);
    return ($retval == 0 ? '<i class="fa fa-lg fa-check text-success"></i>':'<i class="fa fa-lg fa-times text-danger"></i>')." $name<br>";
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
*   Special mode, where slaves are not queeried, before the master commands 
*/
function useLowNetworkMode() {
    $ledger=find_setting_by_name("ledger");
    if(!empty($ledger) && $ledger=="low") {
        return true;
    }
    return false;
}


