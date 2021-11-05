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
    public static $GPIO_MASTER = 140; //NUC980_PE12 - Master Slave switch
    public static $GPIO_FIRMWARE = 140; //NUC980_PE12 - Reset Firmware switch

    public static function outputs() {
        return [
            GVAR::$GPIO_DOOR1,GVAR::$GPIO_DOOR2,GVAR::$GPIO_ALARM1,GVAR::$GPIO_ALARM2,
            GVAR::$RD1_GLED_PIN,GVAR::$RD2_GLED_PIN,GVAR::$BUZZER_PIN
        ];
    } 
}

$masterControllerIp = null;
$inputArray = [
    1 => "/sys/class/gpio/gpio".GVAR::$GPIO_MASTER."/value",
    2 => "/sys/class/gpio/gpio".GVAR::$GPIO_FIRMWARE."/value",
    3 => "/sys/class/gpio/gpio".GVAR::$GPIO_BUTTON1."/value", 
    4 => "/sys/class/gpio/gpio".GVAR::$GPIO_BUTTON2."/value",
    5 => "/sys/class/gpio/gpio".GVAR::$GPIO_DOORSTATUS1."/value", 
    6 => "/sys/class/gpio/gpio".GVAR::$GPIO_DOORSTATUS2."/value"
];

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
    mylog("operateOutput ".$doorId." state=".$state." gpios=".json_encode($gpios));

    $gid = getDoorGPIO($doorId);

    //add gpio for the door to gpios
    //$gpios[] = $gid;
    array_push($gpios, $gid);

    mylog("operateOutput door=".$doorId." state=".$state." gpios=".json_encode($gpios));

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
* This function 
* 
*/
function activateOutput($doorId, $duration, $gpios) {
    mylog("activateOutput door=".$doorId." duration=".$duration." gpios=".json_encode($gpios));
    //open door
    $hasChanged = operateOutput($doorId, 1, $gpios);
    //if the state was not changed, the door was already open. Presumably by the scheduler, or another reader/button
    if($hasChanged) {
        //TODO React here is too much, need simpeler timout?
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
*   Check if the factory reset switch is enabled
*/
function checkIfFactoryReset() {
    return false;
    //return (getGPIO(GVAR::$GPIO_FIRMWARE) == 1);
}
function doFactoryReset() {
    $master = '/maasland_app/www/db/master.db';
    //$file = ' /maasland_app/www/db/prod.db';
    $file = '/maasland_app/www/db/dev.db';
    $backup = '/maasland_app/www/db/prod_bak.db';

    if (!@copy($file, $backup)) {
        $errors= error_get_last();
        mylog("COPY ERROR: ".$errors['type']);
        mylog("<br />\n".$errors['message']);
        mylog(json_encode($errors));
        mylog("failed to make backup $file...\n");
    } elseif (!@copy($master, $file)) {
        mylog(json_encode(error_get_last()));
        mylog("failed to restore factory settings $file...\n");
    } else {
        mylog("Factory settings were restored $file...\n");
    }
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
    global $inputArray;
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
    initGPIO(GVAR::$GPIO_MASTER, false);
    
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

