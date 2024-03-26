<?php
/*
*   GVAR (gpio variables)
*   contains software to hardware tranlations
*/

//get $masterControllerIp global so we can cache it between requests
$masterControllerIp = null;

function outputs() {
    return [
        GVAR::$GPIO_DOOR1,GVAR::$GPIO_DOOR2,GVAR::$GPIO_ALARM1,GVAR::$GPIO_ALARM2,
        GVAR::$RD1_GLED_PIN,GVAR::$RD2_GLED_PIN,GVAR::$BUZZER_PIN,
        GVAR::$RUNNING_LED,GVAR::$OUT12V_PIN
    ];
} 

function inputs() {
    return [
        GVAR::$GPIO_BUTTON1,GVAR::$GPIO_BUTTON2,
        GVAR::$GPIO_DOORSTATUS1,GVAR::$GPIO_DOORSTATUS2,
        GVAR::$GPIO_DOORSTATUS1N,GVAR::$GPIO_DOORSTATUS2N,
        GVAR::$GPIO_MASTER,GVAR::$GPIO_FIRMWARE
    ];
}

//1,2 are the enum for wiegand, gave problems when used
function getInputArray() {
    return [
        //1 => "/sys/class/gpio/gpio".GVAR::$GPIO_MASTER."/value",
        //2 => "/sys/class/gpio/gpio".GVAR::$GPIO_FIRMWARE."/value",
        3 => "/sys/class/gpio/gpio".GVAR::$GPIO_BUTTON1."/value", 
        4 => "/sys/class/gpio/gpio".GVAR::$GPIO_BUTTON2."/value",
        5 => "/sys/class/gpio/gpio".GVAR::$GPIO_DOORSTATUS1."/value", 
        6 => "/sys/class/gpio/gpio".GVAR::$GPIO_DOORSTATUS2."/value"
    ];
}
/*
*   Slaveside methods, also used by the master.
*   - has knowledge which GPIO's belong to what
*   - has functions that call the local hardware
*   - has NO knowledge about content in the database
*
*  inputReceived -> (coap) -> handleInput -> handleUserAccess -> openDoor -> (coap) -> activateOutput
inputReceived 
operateOutput
activateOutput

configureGPIO
setGPIO
getGPIO
initGPIO

resolveInput
getInputValue
*/

/*
*   Operate a door/alarm given a outputId 
*   $outputId : id in the db
*   $state : 0 or 1
*   $gpios : array with extra gpios
*   returns true if state was changed
*/
function operateOutput($outputEnum, $state, $gpios = array()) {
    mylog("operateOutput ".$outputEnum." state=".$state." gpios=".json_encode($gpios));

    $gid = getOutputGPIO($outputEnum);

    //add gpio for the door to gpios
    //$gpios[] = $gid;
    array_push($gpios, $gid);

    mylog("operateOutput output=".$outputEnum." state=".$state." gpios=".json_encode($gpios));

    //check if the state has already been set / door is already open
    $currentValue = getGPIO($gid);
    if($currentValue == $state) {
        return false;
    }
    foreach ($gpios as $gpio) {
        changeDoorState($outputEnum, $state);
    }
    return true;
}

/*
*   Activate a door/alarm given a outputEnum 
*   $outputEnum : id in the db
*   $duration : int in seconds
*   $gpios : array with extra gpios
*   returns true if state was changed
*/
function activateOutput($outputEnum, $duration, $gpios) {
    mylog("activateOutput door=".$outputEnum." duration=".$duration." gpios=".json_encode($gpios));
    //open door
    $hasChanged = operateOutput($outputEnum, 1, $gpios);
    //if the state was not changed, the door was already open. Presumably by the scheduler, or another reader/button
    if($hasChanged) {
        React\EventLoop\Loop::addTimer($duration, function () use ($outputEnum, $gpios) {
            //close door
            operateOutput($outputEnum, 0, $gpios);
            mylog('Done'.PHP_EOL);
        });
    }
    return $hasChanged;
}

function getOutputStatus($outputEnum) {
    mylog("getOutputStatus output=".$outputEnum);
    $gid = getOutputGPIO($outputEnum);
    return getGPIO($gid);
}

/*
*   Handle incomming input during network problems
*   This makes the slave work even if the network is gone
*   resolve user information
*
*   $input : object 
*   $keycode : id in the db
*
*   return json
*
*   Used by inputListener 
*/
function handleInputLocally($input, $keycode) {
    //$controller = find_controller_by_ip($ip);
    $controller = find_controller_by_remarks('this_is_me');
    $duration=find_setting_by_name("door_open");
    mylog(json_encode($controller));
    if(empty($controller)) {
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
                $result = handleUserAccessLocally($user, $input, $controller);
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
            $result = activateOutput($door->enum, $duration, []);
            //$result = openDoor($door, $controller);
            break;
        default:
            error_log("illegal Controller=".$controller->name." input=".$input." keycode=".$keycode);
            $action = "illegal";
            break;
    }    
    //save report
    //saveReport($actor, $action, keyToHex($keycode));
    mylog("handleInput result:".$result);
    return array(
        "actor" =>$actor, 
        "controller" => $controller->name, 
        //"controller" => $controller, 
        "result" => $result
    );
}
function handleUserAccessLocally($user, $readerId, $controller) {
    mylog("handleUserAccessLocally user".$user->name." readerId=".$readerId);
    //Check if user is active
    if(! is_user_active($user) ) {
        return "User is inactive";
    }

    //Determine what door to open
    $door = find_door_for_input_device("reader_".$readerId, $controller->id);
    mylog($door);
    mylog("find_timezone_by_group_id group_id=".$user->group_id);
    mylog("find_timezone_by_group_id door_id=".$door->id);

    //check if the group/user has access for this door
    $tz = find_timezone_by_group_id($user->group_id, $door->id);
    mylog("tz=".json_encode($tz));
    if(empty($tz)) {
        return "Door can not be used. No timezone assigned to this door for this group.";
    }
    mylog("group=".$user->group_id." door=".$door->id."=".$door->name);
    mylog("name=".$tz->name." start=".$tz->start." end=".$tz->end);

    //open the door 
    //$msg = openDoor($door, $controller);
    $duration=find_setting_by_name("door_open");
    $msg = activateOutput($door->enum, $duration, []);
    $msg = $door->name;//."@".$controller->name;
    return $msg;    
}

/*
*   Check if the factory reset switch is enabled
*/
function checkIfFactoryReset() {
    return (getGPIO(GVAR::$GPIO_FIRMWARE) == 0);
}
function doFactoryReset() {
    mylog("Factory reset invoked");
    $master = '/maasland_app/www/db/master.db';
    $file = '/maasland_app/www/db/prod.db';
    //$file = '/maasland_app/www/db/dev.db';
    $backup = '/maasland_app/www/db/prod_bak.db';

    //this method can only be invoked from cli (inputListener), webserver has no permission to write files
    if (!@copy($file, $backup)) {
        $errors= error_get_last();
        mylog("COPY ERROR: ".$errors['type']);
        mylog("<br />".$errors['message']);
        mylog(json_encode($errors));
        mylog("failed to make backup $file...");
    } elseif (!@copy($master, $file)) {
        mylog(json_encode(error_get_last()));
        mylog("failed to restore factory settings $file...");
    } else {
        //set proper file permissions after creation 
        chmod($file,0775);
        mylog("Factory settings were restored $file...");
    }

    //put network setting back to default/dhcp
    updateNetworkMakeDHCP();
    //put master IP back to automatic / remove .extensions.php
    updateMasterIP(false);

    //turn on led to signal ready, on = 0
    exec("echo 0 >/sys/class/gpio/gpio".GVAR::$RUNNING_LED."/value");
    mylog("Finished doFactoryReset. Turned on running led.");
}

/*
*   Check if this controller is Master
*   Slave controllers don't use database and webgui
*   if Master => S1 Value is 0
*/
function checkIfMaster() {
    //mylog("checkIfMaster: Switch=".getGPIO(GVAR::$GPIO_MASTER));
    return (getGPIO(GVAR::$GPIO_MASTER) == 0);
}
function getMasterControllerIP($masterIpOverwrite = false) {
    //return "192.168.178.137";
    global $masterControllerIp;
    if($masterIpOverwrite) {
        mylog("masterIp Overwrite=".$masterIpOverwrite);
        $masterControllerIp = $masterIpOverwrite;
    }
    if(checkIfMaster()) {
        //Design decision not to put the network IP in the db for Master
        //This means, the Master does not know it's own IP at start
        //And will even work if it get's an other IP through DHCP 
        //Announcement of this IP too the slave wil be through mDNS
        //This makes the setup flexible. 
        //But also means Master needs to call localhost, when doing an apiCall.        
        return "127.0.0.1";
    }
    if( $masterControllerIp == null ) {
        //TODO too errorprone fishing from an array? No other way... 
        //["=","eth0","IPv4","FlexessDuo","_maasland._udp","local","FlexessDuo-2.local","192.168.178.179","5683","text"]
        //3=hostname,7=ip
        $masterControllerIp = searchMasterControllerIP();    

        if(empty($masterControllerIp)) {            
            error_log("ERROR: Master Controller not found :\n");
            blinkMessageLed(5);
        } else {
            //turn led on, too indicate everyting is ok, on = 0
            exec("echo 0 >/sys/class/gpio/gpio".GVAR::$RUNNING_LED."/value");
        }
        return $masterControllerIp;
    } else {
        return $masterControllerIp;
    }
}
function searchMasterControllerIP(){
    $result = mdnsBrowse("_master._sub._maasland._udp");
    mylogError(json_encode($result)."\n");
    if(isset($result[0][7])) {
        return $result[0][7];
    }
    return null;
}
function getMasterURL() {
    return "http://".getMasterControllerIP()."/";
}

/*
*   Hardware translate functions 
*/

/*
*   Open or close a door
*   $outputEnum : doors.enum in database in accordance with physical connection
*   $state : 0 = close, 1 = open
*/
function changeDoorState($outputEnum, $state) { 
    //switch lock
    $gid = getOutputGPIO($outputEnum);         
    setGPIO($gid, $state);

    //change led on the reader
    if($outputEnum == 1) {
        setGPIO(GVAR::$RD1_GLED_PIN, $state);
    } else {
        setGPIO(GVAR::$RD2_GLED_PIN, $state);
    }
    return $state;
}

/*
*   Get GPIO value for a output relais
*   $outputEnum : doors.enum in database in accordance with physical connection
*/
function getOutputGPIO($outputEnum) { 
    //mylog("getOutputGPIO=".$outputEnum);
    if($outputEnum == "1") return GVAR::$GPIO_DOOR1;
    if($outputEnum == "2") return GVAR::$GPIO_DOOR2;
    if($outputEnum == "3") return GVAR::$GPIO_ALARM1;
    if($outputEnum == "4") return GVAR::$GPIO_ALARM2;
    return 0;
}

/*
*   Give an input number for a gpio path 
*   1,2 wiegand reader
*   3,4 button
*   5,6 door sensor
*/
function resolveInput($gpioPath) {    
    $inputArray = getInputArray();
    return array_search($gpioPath,$inputArray);
    //if($gpioPath == "/sys/class/gpio/gpio170/value") return 3;
    //if($gpioPath == "/sys/class/gpio/gpio169/value") return 4;
}

function getInputValue($gpioPath) {
    return file_get_contents($gpioPath);
    //return exec("cat ".$gpioPath);
}





/*
*   GPIO helper functions 
*/
function configureGPIO() {
    //Read env file
    Arrilot\DotEnv\DotEnv::load('/maasland_app/www/.env.php'); 
    $debug = Arrilot\DotEnv\DotEnv::get('APP_DEBUG', false);
    $logLevel = Arrilot\DotEnv\DotEnv::get('APP_LOG_LEVEL', false);
    $hardwareVersion = Arrilot\DotEnv\DotEnv::get('HARDWARE_VERSION', false);
    option('debug', $debug);
    option('log_level', $logLevel);
    option('hardware_version', $hardwareVersion);
    option('session', 'Maasland_Match_App');  

    Arrilot\DotEnv\DotEnv::load('/maasland_app/www/.extensions.php');
    $masterIpOverwrite = Arrilot\DotEnv\DotEnv::get('MASTER_IP', false);

    //web loads dynamically scripts need to set this manualy
    mylog("Hardware version=".$hardwareVersion);
    if($hardwareVersion == 1) {
        require_once '/maasland_app/www/db/gvar.match2.php';
    } else {
        require_once '/maasland_app/www/db/gvar.match4.php';
    }

    //init inputs and outputs
    foreach (outputs() as $gpio) {
        initGPIO($gpio);
    }
    foreach (inputs() as $gpio) {
        initGPIO($gpio, false);
    }
    mylog("Activate wiegand readers");
    setGPIO(GVAR::$OUT12V_PIN, 1);

    //we need it, might as well get it already
    getMasterControllerIP($masterIpOverwrite);

    return "Board identified as :".GVAR::$BOARD_TYPE.
        "GPIOInputs initialized: Controller configured as ".
        (checkIfMaster() ? "Master" : "Slave")."\n";
}

function setGPIO($gpio, $state) {
    if (! in_array($gpio, outputs())) {
        mylog("setGPIO ".$gpio." not an output");
        return 0;
    }
    mylog("setGPIO ".$gpio."=".$state);
    initGPIO($gpio);
    exec("echo ".$state." >/sys/class/gpio/gpio".$gpio."/value");   
    return 1;    
}
function getGPIO($gpio) {
    if (! in_array( $gpio, array_merge( inputs(), outputs() ))) {
        mylog("getGPIO ".$gpio." not an input");
        return 0;
    }
    $v = file_get_contents("/sys/class/gpio/gpio".$gpio."/value");
    //$v = exec("cat /sys/class/gpio/gpio".$gpio."/value");
    //mylog("getGPIO ".$gpio."=".$v);
    return trim($v);
}
function initGPIO($gpio, $out = true) {
    if(! file_exists("/sys/class/gpio/gpio".$gpio)) {
        mylog("init gid=".$gpio);
        exec("echo ".$gpio." > /sys/class/gpio/export");
        if($out) {
            exec("echo out >/sys/class/gpio/gpio".$gpio."/direction"); 
        } else {
           exec("echo in >/sys/class/gpio/gpio".$gpio."/direction"); 
           //input edge needs to be set to both, so inotify can be used
           exec("echo both >/sys/class/gpio/gpio".$gpio."/edge"); 
        }
    }
}

