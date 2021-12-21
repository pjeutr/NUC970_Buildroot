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
        GVAR::$GPIO_BUTTON1,GVAR::$GPIO_BUTTON2,GVAR::$GPIO_DOORSTATUS1,GVAR::$GPIO_DOORSTATUS2,
        GVAR::$GPIO_MASTER,GVAR::$GPIO_FIRMWARE
    ];
}

function getInputArray() {
    return [
        1 => "/sys/class/gpio/gpio".GVAR::$GPIO_MASTER."/value",
        2 => "/sys/class/gpio/gpio".GVAR::$GPIO_FIRMWARE."/value",
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
function operateOutput($outputId, $state, $gpios = array()) {
    mylog("operateOutput ".$outputId." state=".$state." gpios=".json_encode($gpios));

    $gid = getDoorGPIO($outputId);

    //add gpio for the door to gpios
    //$gpios[] = $gid;
    array_push($gpios, $gid);

    mylog("operateOutput door=".$outputId." state=".$state." gpios=".json_encode($gpios));

    //check if the state has already been set / door is already open
    $currentValue = getGPIO($gid);
    if($currentValue == $state) {
        return false;
    }
    foreach ($gpios as $gpio) {
        setGPIO($gpio, $state);
    }
    return true;
}

/*
*   Activate a door/alarm given a outputId 
*   $outputId : id in the db
*   $duration : int in seconds
*   $gpios : array with extra gpios
*   returns true if state was changed
*/
function activateOutput($outputId, $duration, $gpios) {
    mylog("activateOutput door=".$outputId." duration=".$duration." gpios=".json_encode($gpios));
    //open door
    $hasChanged = operateOutput($outputId, 1, $gpios);
    //if the state was not changed, the door was already open. Presumably by the scheduler, or another reader/button
    if($hasChanged) {
        //get instance for THE eventloop
        $loop = React\EventLoop\Loop::get();
        $loop->addTimer($duration, function () use ($outputId, $gpios) {
            //close door
            operateOutput($outputId, 0, $gpios);
            mylog('Done'.PHP_EOL);
        });
    }
    return $hasChanged;
}

function getOutputStatus($outputId) {
    mylog("getOutputStatus output=".$outputId);
    $gid = getDoorGPIO($outputId);
    return getGPIO($gid);
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

    //this method can only be invoked from cli (inputListner), webserver has no permission to writh files
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
        mylog("Factory settings were restored $file...");
    }
}

/*
*   Get available controllers to command for the master
*   -
*/
function available_controllers() {
    $result = mdnsBrowse("_maasland._udp");
    mylog($result);
    //Remove master controller
    $masterIp = mdnsBrowse("_master._sub._maasland._udp")[0][7];
    $result = array_filter($result, function($v) use($masterIp){ 
        mylog($v[7] ."". $masterIp);
        return $v[7] != $masterIp; 
    });
    return json($result);
}

/*
*   Check if this controller is Master
*   Slave controllers don't use database and webgui
*   if Master => S1 Value is 0
*/
function checkIfMaster() {
    //mylog("checkIfMaster: S=".getGPIO(GVAR::$GPIO_MASTER));
    return (getGPIO(GVAR::$GPIO_MASTER) == 0);
}

function getMasterControllerIP() {
    //return "192.168.178.137";
    global $masterControllerIp;
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
        //TODO too errorprone fishing from an array?
        //["=","eth0","IPv4","FlexessDuo","_maasland._udp","local","FlexessDuo-2.local","192.168.178.179","5683","text"]
        //3=hostname,7=ip
        $result = mdnsBrowse("_master._sub._maasland._udp");
        mylog(json_encode($result)."\n");
        $masterControllerIp = $result[0][7];
        if(empty($masterControllerIp)) {
            die ("ERROR: Master Controller not found :".json_encode($result)."\n");
            //TODO restart coap_listener.php
        }
        return $masterControllerIp;
    } else {
        return $masterControllerIp;
    }
}

function getMasterURL() {
    //TODO make dynamic, called form slave error page
    //return "http://flexessduo.local/";
    return "http://".getMasterControllerIP()."/";
}

/*
*   Hardware translate functions 
*/

/*
*   Get GPIO value for a door relais
*   $outputId : doors.id in database in accordance with physical connection
*/
function getDoorGPIO($doorEnum) { 
    //mylog("getDoorGPIO=".$doorEnum);
    if($doorEnum == "1") return GVAR::$GPIO_DOOR1;
    if($doorEnum == "2") return GVAR::$GPIO_DOOR2;
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
    return exec("cat ".$gpioPath);
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
    option('debug', isset($debug));
    option('log_level', $logLevel);
    option('hardware_version', $hardwareVersion);
    option('session', 'Maasland_Match_App');  

    //web loads dynamically scripts need to set this manualy
    mylog("H=".$hardwareVersion);
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
    setGPIO(GVAR::$RUNNING_LED, 1);

    //TODO fill cache?
    //global $inputArray?
    //we need it, might as well get it already
    getMasterControllerIP();

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
    $v = exec("cat /sys/class/gpio/gpio".$gpio."/value");
    //mylog("getGPIO ".$gpio."=".$v);
    return $v;
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

