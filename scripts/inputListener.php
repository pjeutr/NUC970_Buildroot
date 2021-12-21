#!/usr/bin/php
<?php

/*
* Call appropiate script for Master or Slave
* TODO could combine both scripts again, instead minify?
* - harder to read/test
* - not sure Master resources are loaded on Slave
*
* coap-client -m get coap://192.168.178.179/input/1/3333
* coap-client -m get coap://192.168.178.137/status/66-68 = print button status 
* coap-client -m get coap://192.168.178.137/output/2/1 = Door 2 for open
* coap-client -m get coap://192.168.178.137/output/1/0 = Door 1 for close
* coap-client -m get coap://192.168.178.137/activate/1/2 = Door 1 for 2 seconds
* coap-client -m get coap://192.168.178.139/activate/2/5/3-11 = Door 2 all leds and buzzer for 5 seconds
*
* curl http://192.168.178.179/?/api/activate/1/2/2-10
* curl http://192.168.178.179/?/api/input/1/3333
*
* TODO user auth?
* coap-client -m get -u maaslnd -k WGH coap://slave/activate/2/5/2-10-66-79
*/
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/limonade.php';
require_once '/maasland_app/www/lib/logic.slave.php';
require_once '/maasland_app/vendor/arrilot/dotenv-php/src/DotEnv.php';

//configure and initialize gpio 
echo configureGPIO();

//check and do restore factory settings
if(checkIfFactoryReset()){
	doFactoryReset();
}

//create THE eventloop. (get's instance, or creates new)
$loop = React\EventLoop\Loop::get();

if( checkIfMaster() ) {
	echo "Master input script loaded\n";
	require_once '/maasland_app/scripts/coapListenerMaster.php';
} else {
	echo "Slave input script loaded\n";
	require_once '/maasland_app/scripts/coapListener.php';
}

//Start THE eventloop
$loop->run();






//not used, future callapi?
function callApiOLD($input, $data) {
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

