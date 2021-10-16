#!/usr//bin/php
<?php

/*
* Runs on all controllers
* - detects input changes (readers/buttons/sensors)
* - listen for coap messages (to open door)
* If master controller
* - publish through mDNS as master
* - listen for coap messages (reader was used)
*/

require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/limonade.php';;
require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/lib/logic.slave.php';

//initialize database connection
$dsn = "sqlite:/maasland_app/www/db/dev.db";
$db = new PDO($dsn);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
option('dsn', $dsn);
option('db_conn', $db);
option('debug', true);

echo configureGPIO();

if( checkIfMaster() ) {
	require_once '/maasland_app/www/lib/logic.door.php';
	//load models for used db methods
	require_once '/maasland_app/www/lib/model.report.php';
	require_once '/maasland_app/www/lib/model.user.php';
	require_once '/maasland_app/www/lib/model.settings.php';
	require_once '/maasland_app/www/lib/model.door.php';
	require_once '/maasland_app/www/lib/model.controller.php';
	require_once '/maasland_app/www/lib/model.timezone.php';
	require_once '/maasland_app/www/lib/model.rule.php';
	echo "Extra Master requirements loaded\n";

	//anounce as master server
	$r = mdnsPublish();
	mylog("mdnsPublish return=".json_encode($r));
}

//get ip
$ifconfig = shell_exec('/sbin/ifconfig eth0');
preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
$thisControllerIp = $match[1];
mylog("thisControllerIp=".$thisControllerIp."\n");

//start coap server
$loop = React\EventLoop\Factory::create();
$server = new PhpCoap\Server\Server( $loop );
$server->receive( 5683, $thisControllerIp);

//get master ip TODO not used?
// $masterControllerIp = $thisControllerIp;
// if( !checkIfMaster() ) {
// 	$masterControllerIp = getMasterControllerIP();
// }
// mylog("masterControllerIp=".$masterControllerIp."\n");


/*
* coap-client -m get coap://192.168.178.137/status/170-169 = print button status 
* coap-client -m get coap://192.168.178.137/input/1/1234 
* coap-client -m get coap://192.168.178.137/output/2/1 = Door 2 for open
* coap-client -m get coap://192.168.178.137/output/1/0 = Door 1 for close
* TODO-coap-client -m get coap://192.168.178.137/output/1/2|on|off/2-10-66-79 = Door 1 for 2 seconds
* coap-client -m get coap://192.168.178.137/activate/1/2 = Door 1 for 2 seconds
* coap-client -m get coap://192.168.178.137/activate/2/5/2-10-66-79 = Door 2 all leds and buzzer for 5 seconds
*
* TODO user auth?
* coap-client -m get -u maaslnd -k WGH coap://slave/activate/2/5/2-10-66-79
*/

/*
* Helper to read options from the request (ReactPHP specific)
* to prevent errors, check if an option exist before reading the value
*/	
function readOption($option, $i) {
	if (array_key_exists($i, $option)) {
		return $option[$i] ? $option[$i]->getValue() : null;
	}
	return null;
}

/*
* Listen for input changes (inputListener)
*/
//TODO class meegeven werkte niet, daarom maar de index van array
//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, EpollWait::class);
$wiegandObserver = new \Calcinai\Rubberneck\Observer($loop, 0);
$wiegandObserver->onModify(function($file_name){
	//mylog("Modified:". $file_name. "\n");
	//determine the input number for this file
	$input = resolveInput($file_name);
	//find the value
	$value = getInputValue($file_name);
	//mylog("value:". $value. "\n");

	$parts = explode(':',$value);
	$nr = $parts[0];
	$keycode = $parts[1];
	$reader = $parts[2];
	mylog("Wiegand:". $reader.":".$keycode);
	$result = inputReceived($reader, $keycode);
	mylog(json_encode($result));
});	
//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, InotifyWait::class);
$inputObserver = new \Calcinai\Rubberneck\Observer($loop, 1);
$inputObserver->onModify(function($file_name){
	//mylog("Modified:". $file_name. "\n");
	//determine the input number for this file
	$input = resolveInput($file_name);
	//find the value
	$value = getInputValue($file_name);
	//mylog("value:". $value. "\n");
	//take action if a button is pressed
	if($value == 1) { 
		mylog("Button:". $input);
		$result =  inputReceived($input, "");
		mylog(json_encode($result));
	}   
	//TODO sleep / prevent klapperen 
	sleep(1);
});
//Declare inputs to observe
//$observer->watch('/dev/wiegand'); 
//$observer->watch('/sys/kernel/wiegand/read'); 
//$observer->watch('/sys/class/wiegand/value'); 
//maybe adding a newline? or write at a different place. not in sys
//$observer->watch('/var/log/messages');
$wiegandObserver->watch('/sys/kernel/wiegand/read');
$inputObserver->watch('/sys/class/gpio/gpio170/value');
//$observer->watch('/sys/class/gpio/gpio170/value');
//$observer->watch('/sys/class/gpio/gpio68/value');




/*
* Run coapServer / coapListener
*/	
$server->on( 'request', function( $req, $res, $handler ) use ($loop){
	$o = $req->GetOptions();
	$type = readOption($o,0);
	$result = 'dummy';
	if($type == 'input') { //ON MASTER
		$from = $handler->getPeerHost();
		$input = readOption($o,1);
		$data = readOption($o,2);
		mylog("coapServer: ".checkIfMaster()." input=".$input." data=".$data);
		$result = handleInput($from, $input, $data);
	/*
	* TODO split up?
	* Above requests will only happen on master
	* Following request will happen on slave and master 
	*/	
	} elseif($type == 'output') {
		$from = $handler->getPeerHost(); //will allways be master
		$door_id = readOption($o,1);
		$state = readOption($o,2);
		$gpios = explode("-", readOption($o,3));
		mylog("coapListener: Open door ".$door_id." state=".$state."s gpios=".json_encode($gpios));
		$result = operateOutput($door_id, $state, $gpios);
	} elseif($type == 'activate') { 
		$from = $handler->getPeerHost(); //will allways be master
		$door_id = readOption($o,1);
		$duration = readOption($o,2);
		$gpios = explode("-", readOption($o,3));
		mylog("coapListener: Activate door ".$door_id." for ".$duration."s gpios=".json_encode($gpios));
		$result = activateOutput($door_id, $duration, $gpios);
	} elseif($type == 'status') { 
		$from = $handler->getPeerHost(); //will allways be master
		$gpios = explode("-", readOption($o,1));
		mylog("coapListener: Status gpios=".json_encode($gpios));
		$result = array();
		foreach ($gpios as $gpio) {
        	$result[] = array($gpio => getGPIO($gpio));
    	}
    } elseif($type == 'devices') { //debug helper
		$result = readOption($o,1);
    } elseif($type == 'data') { //debug helper
		$result = readOption($o,1);		
	} else {
		$result = "invalid request";
	}
	$res->setPayload( json_encode( $result ) );
	$handler->send( $res );
});

$loop->run();
