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
    public static $RD1_GLED_PIN = 2; //NUC980_PA2   //reader1 gled output
    public static $RD2_GLED_PIN = 10;  //NUC980_PA10  //reader2 gled output
    public static $BUZZER_PIN = 79;  //NUC980_PC15  //buzzer output

    //inputs
    public static $GPIO_BUTTON1 = 170; //NUC980_PF10
    public static $GPIO_BUTTON2 = 169; //NUC980_PF9 - CAT_PIN //contact input
    public static $GPIO_DOORSTATUS1 = 170;//168; //NUC980_PF8 - PSU_PIN //psu input
    public static $GPIO_DOORSTATUS2 = 45; //NUC980_PB13 - TAMPER_PIN //tamp input
    public static $GPIO_S1 = 140; //NUC980_PE12 - Master Slave switch

    public static function outputs() {
        return [
            GVAR::$GPIO_DOOR1,GVAR::$GPIO_DOOR2,GVAR::$GPIO_ALARM1,GVAR::$GPIO_ALARM2,
            GVAR::$RD1_GLED_PIN,GVAR::$RD2_GLED_PIN,GVAR::$BUZZER_PIN
        ];
    } 
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
    //mylog("checkIfMaster: S=".getGPIO(GVAR::$GPIO_S1));
    return (getGPIO(GVAR::$GPIO_S1) == 0);
}

function getMasterControllerIP() {
    //TODO cache / make singleton?
    //TODO too errorprone fishing from an array

    //if( !checkIfMaster() ) {
        $result = mdnsBrowse("_master._sub._maasland._udp");
        mylog(json_encode($result)."\n");
        $masterControllerIp = $result[0][7];
        if(empty($masterControllerIp)) {
            die ("ERROR: Master Controller not found :".json_encode($result)."\n");
            //TODO restart coap_listener.php
        }
        return $masterControllerIp;
    //}

    //return "192.168.178.137";
}

function getMasterURL() {
    //TODO make dynamic, called form slave error page
    //return "http://flexessduo.local/";
    return "http://".getMasterControllerIP()."/";
}


function inputReceived($input, $data) {
    mylog((checkIfMaster() ? 'Master' : 'Slave' )." inputReceived:".$input);
    if ( checkIfMaster() ) {
        return handleInput(getMasterControllerIP(), $input, $data);
    } else {
        //tunnel through coap to the master where handleInput is called
        return makeInputCoapCall($input."/".$data);
    }
}

//TODO ^
function makeInputCoapCall($uri) {
    $url = "input/".$uri;
    mylog($url);
    $msg = apiCall(getMasterControllerIP(), $url);
    mylog($msg);
    return $msg;
}






/*
*   Hardware translate functions 
*/

/*
*   Get GPIO value for a door relais
*   $doorId : doors.id in database in accordance with physical connection
*/
function getDoorGPIO($doorEnum) { 
    mylog("getDoorGPIO=".$doorEnum);
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
*   GPIO helper functions 
*/
function configureGPIO() {
    //TODO loop over outputs array
    initGPIO(GVAR::$GPIO_DOOR1);
    initGPIO(GVAR::$GPIO_DOOR2);
    initGPIO(GVAR::$GPIO_ALARM1);
    initGPIO(GVAR::$GPIO_ALARM2);
    initGPIO(GVAR::$RD1_GLED_PIN);
    initGPIO(GVAR::$RD2_GLED_PIN);
    initGPIO(GVAR::$BUZZER_PIN);

    initGPIO(GVAR::$GPIO_BUTTON1, false);
    initGPIO(GVAR::$GPIO_BUTTON2, false);
    initGPIO(GVAR::$GPIO_DOORSTATUS1, false);
    initGPIO(GVAR::$GPIO_DOORSTATUS2, false);
    initGPIO(GVAR::$GPIO_S1, false);
    
    return "GPIOInputs initialized: Controller configured as ".(checkIfMaster() ? "Master" : "Slave")."\n";
}

function setGPIO($gpio, $state) {
    if (! in_array($gpio, GVAR::outputs())) {
        mylog("setGPIO ".$gpio." not an output\t");
        return 0;
    }
    mylog("setGPIO ".$gpio."=".$state."\t");
    initGPIO($gpio);
    exec("echo ".$state." >/sys/class/gpio/gpio".$gpio."/value");   
    return 1;    
}
function getGPIO($gpio) {
    return exec("cat /sys/class/gpio/gpio".$gpio."/value");
}
function initGPIO($gpio, $out = true) {
    if(! file_exists("/sys/class/gpio/gpio".$gpio)) {
        mylog("init gid=".$gpio."\t");
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

