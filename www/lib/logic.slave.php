<?php
/*
*   GVAR (gpio variables)
*   contains software to hardware tranlations
*/
class GVAR
{
    //outputs
    public static $GPIO_DOOR1 = 68; //NUC980_PC4
    public static $GPIO_DOOR2 = 66; //NUC980_PC2
    public static $GPIO_ALARM1 = 65; //NUC980_PC2
    public static $GPIO_ALARM2 = 66; //fake same as door

    //inputs
    public static $GPIO_BUTTON1 = 170; //NUC980_PF10
    public static $GPIO_BUTTON2 = 169; //NUC980_PF9 - CAT_PIN //contact input
    public static $GPIO_DOORSTATUS1 = 170;//168; //NUC980_PF8 - PSU_PIN //psu input
    public static $GPIO_DOORSTATUS2 = 45; //NUC980_PB13 - TAMPER_PIN //tamp input

    public static $GPIO_S1 = 140; //NUC980_PE12 - Master Slave switch

    public static $RD1_GLED_PIN = 2; //NUC980_PA2   //reader1 gled output
    public static $RD2_GLED_PIN = 10;  //NUC980_PA10  //reader2 gled output

    public static $BUZZER_PIN = 79;  //NUC980_PC15  //buzzer output
}

/*
*   Slaveside methods, also used by the master.
*   - has knowledge which GPIO's belong to what
*   - has functions that call the local hardware
*   - has NO knowledge about content in the database
*/
function setupGPIOInputs() {
    //
    initGPIO(GVAR::$GPIO_BUTTON1);
    initGPIO(GVAR::$GPIO_BUTTON2);
    initGPIO(GVAR::$GPIO_DOORSTATUS1);
    initGPIO(GVAR::$GPIO_DOORSTATUS2);
    initGPIO(GVAR::$BUZZER_PIN);
    initGPIO(GVAR::$GPIO_S1);
    initGPIO(GVAR::$RD1_GLED_PIN);
    initGPIO(GVAR::$RD2_GLED_PIN);
    return "GPIOInputs initialized: Controller configured as ".(checkIfMaster() ? "Master" : "Slave")."\n"  ;
}

/*
*   Give an input number for a gpio path 
*   1,2 wiegand reader
*   3,4 button
*   5,6 door sensor
*/
function resolveInput($gpioPath) {
    $inputArray = [
        1 => "/var/log/messages",
        3 => "/sys/class/gpio/gpio".GVAR::$GPIO_BUTTON1."/value", 
        4 => "/sys/class/gpio/gpio".GVAR::$GPIO_BUTTON2."/value",
        5 => "/sys/class/gpio/gpio".GVAR::$GPIO_DOORSTATUS1."/value", 
        6 => "/sys/class/gpio/gpio".GVAR::$GPIO_DOORSTATUS2."/value"
    ];
    return array_search($gpioPath,$inputArray);
    //if($gpioPath == "/sys/class/gpio/gpio170/value") return 3;
    //if($gpioPath == "/sys/class/gpio/gpio169/value") return 4;
}

function getInputValue($gpioPath) {
    return exec("cat ".$gpioPath);
}

/*
*   Operate a door/alarm given a doorId 
*   $doorId : id in the db
*   $state : 0 or 1
*   $gpios : array with extra gpios
*   returns true if state was changed
*/
function operateOutput($doorId, $state, $gpios = array()) {
    mylog("operateOutput ".$doorId." state=".$state);

    $gid = getDoorGPIO($doorId);

    //add gpio for the door to gpios
    $gpios[] = $gid;
    mylog("operateOutput door=".$doorId." open=".$state."s gpios=".json_encode($gpios));

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
* This function will become obsolete
* The timer can be block a request move timer to coap/react server, with a promise?
*/
function activateOutput($doorId, $duration, $gpios) {
    mylog("activateOutput door=".$doorId." duration=".$duration." gpios=".json_encode($gpios));
    //open door
    $hasChanged = operateOutput($doorId, 1, $gpios);
    //if the state was not changed, the door was already open. Presumably by the scheduler, or another reader/button
    if($hasChanged) {
        $loop = React\EventLoop\Factory::create();
        $loop->addTimer($duration, function () use ($doorId, $gpios) {
            //close door
            operateOutput($doorId, 0, $gpios);
            mylog('Done'.PHP_EOL);
        });
        $loop->run();
    }
    return $hasChanged;
}



/*
*   Check if this controller is Master
*   Slave controllers don't use database and webgui
*   if Master => S1 Value is 0
*/
function checkIfMaster() {
    mylog("checkIfMaster:".getGPIO(GVAR::$GPIO_S1));
    return (getGPIO(GVAR::$GPIO_S1) == 0);
}

function getMasterControllerIP() {
    //TODO cache / make singleton?
    //TODO to errorprone fishing from an array

    //if( !checkIfMaster() ) {
        $result = mdnsBrowse("_master._sub._maasland._udp");
        mylog(json_encode($result)."\n");
        $masterControllerIp = $result[0][7];
        return $masterControllerIp;
    //}

    //TODO get real master ip, through d
    //return "192.168.178.137";
}

function getMasterURL() {
    //TODO make dynamic, called form slave error page
    return "http://".getMasterControllerIP()."/";
}


function inputReceived($input, $data) {
    mylog("inputReceived:".$input);
    if ( checkIfMaster() ) {
        return handleInput(getMasterControllerIP(), $input, $data);
    } else {
        return makeInputCoapCall($doorId."/".$duration."/".implode("-",$gpios));
    }
}

function makeInputCoapCall($uri) {
    //input events are always going to the master
    $cmd = "coap-client -m get coap://".getMasterControllerIP()."/input/".$uri;
    mylog("makeInputCoapCall:".$cmd);
    return shell_exec($cmd);
}

/*
*   Get GPIO value for a door relais
*   $doorId : doors.id in database in accordance with physical connection
*/
function getDoorGPIO($doorId) { 
    mylog("getDoorGPIO=".$doorId);
    if($doorId == "1") return GVAR::$GPIO_DOOR1;
    if($doorId == "2") return GVAR::$GPIO_DOOR2;
    return 0;
}

/*
*   GPIO setter and getter
*/
function setGPIO($gpio, $state) {
    mylog("setGPIO ".$gpio."=".$state."\t");
    initGPIO($gpio);
    exec("echo ".$state." >/sys/class/gpio/gpio".$gpio."/value");   
    return 1;    
}
function getGPIO($gpio) {
    return exec("cat /sys/class/gpio/gpio".$gpio."/value");
}
function initGPIO($gpio) {
    if(! file_exists("/sys/class/gpio/gpio".$gpio)) {
        mylog("init gid=".$gpio."\t");
        exec("echo ".$gpio." > /sys/class/gpio/export");
        exec("echo out >/sys/class/gpio/gpio".$gpio."/direction");    
    }
}

