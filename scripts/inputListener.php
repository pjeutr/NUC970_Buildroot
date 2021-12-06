#!/usr//bin/php
<?php

/*

* Second script, only inputListener 
* coapServer & Listener, could not multitask
* So the webapi is used instead

* Runs on all controllers
* - detects input changes (readers/buttons/sensors)
* 
*/

require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/limonade.php';;
require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/lib/logic.slave.php';

//configure and initialize gpio 
echo configureGPIO();

//check and do restore factory settings
if(checkIfFactoryReset()){
	doFactoryReset();
}

if( checkIfMaster() ) {
	//initialize database connection
	configDB();

	require_once '/maasland_app/www/lib/logic.master.php';
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

function callApi($input, $data) {
	global $loop;

    mylog((checkIfMaster() ? 'Master' : 'Slave' )." inputReceived:".$input." data=".$data);
/* 	remove local calls on master, because it's not performing/multitasking
	everything goes through the webserver that can handle multiple calls
	untill there is a good coapserver?
    if ( checkIfMaster() ) {
        //return handleInput(getMasterControllerIP(), $input, $data);
        return handleInput("master", $input, $data);
    } else {
        //tunnel through coap to the master where handleInput is called
        //return makeInputCoapCall($input."/".$data);

    	if(true) {
*/		
	    	$url = "http://".getMasterControllerIP()."/?/api/input/".$input."/".$data;
	        mylog("apiCall:".$url);

			$client = new React\HttpClient\Client( $loop );
			$request = $client->request('GET', $url);
			$request->on('response', function ( $response ) {
			    $response->on('data', function ( $data ) {
			    	mylog("apiCall return=".json_encode($data));
			        return $data;
			    });
			});
			$request->end();
/*
    	} else {
	        $url = "coap://".getMasterControllerIP()."/input/".$input."/".$data;
	        mylog("coapCall:".$url);
	        //request
	        $client = new PhpCoap\Client\Client( $loop );
	        #Er is een bug bij client-get, zelf fixen?
	        #https://github.com/cfullelove/PhpCoap/issues/5
			$client->get($url, function( $data ) {
				mylog("coapCall return=".json_encode($data));
			    return $data;
			});
		}
    }
*/    
}

$loop = React\EventLoop\Factory::create();

/*
* Listen for input changes (inputListener)
*/
//TODO class meegeven werkte niet, daarom maar de index van array
//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, EpollWait::class);
$wiegandObserver = new \Calcinai\Rubberneck\Observer($loop, 0);
$wiegandObserver->onModify(function($file_name){
	//find the value
	$value = getInputValue($file_name);
	$parts = explode(':',$value);
	$nr = $parts[0];
	$keycode = $parts[1];
	$reader = $parts[2];
	mylog("Wiegand:". $reader.":".$keycode);
	$result =  callApi($reader, $keycode);
    mylog(json_encode($result));
});	

//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, InotifyWait::class);
$inputObserver = new \Calcinai\Rubberneck\Observer($loop, 1);
$inputObserver->onModify(function($file_name){
	mylog("Modified:". $file_name. "\n");
	//determine the input number for this file
	$input = resolveInput($file_name);
	//find the value
	$value = getInputValue($file_name);
	//mylog("value:". $value. "\n");
	//take action if a button is pressed
	if($value == 1) { 
		mylog("Button:". $input);
		$result =  callApi($input, "");
        mylog(json_encode($result));
	}   
	//TODO sleep / prevent klapperen 
	//sleep(1);
});

//listen voor gpio inputs
global $inputArray;
foreach ($inputArray as $value) {
	mylog("inputObserver init:". $value ." \n");
    $inputObserver->watch($value);
}
//$inputObserver->watch('/sys/class/gpio/gpio170/value');

//listen voor wiegand readers
$wiegandObserver->watch('/dev/wiegand');

$loop->run();
