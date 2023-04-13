#!/usr/bin/php
<?php

/*
* Call appropiate script for Master or Slave
* TODO could combine both scripts again, instead minify?
* - harder to read/test
* - not sure Master resources are loaded on Slave
*
* coap-client -m get coap://192.168.178.179/input/1/2310811
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
require_once '/maasland_app/vendor/pjeutr/dotenv-php/src/DotEnv.php';

//configure and initialize gpio 
echo configureGPIO();

//check and do restore factory settings
if(checkIfFactoryReset()){
	echo "Factory reset is invoked\n";
	doFactoryReset();
	echo "Factory has finished\n";
}

//check if network is availabel
$localIP = exec("ifconfig eth0 | awk '/inet addr/ {gsub(\"addr:\", \"\", $2); print $2}'");
if( empty($localIP) ) {
	echo "No network connection\n";
	blinkMessageLed(1);
} else {
	echo "Network ip=$localIP\n";
	//turn on running led
	exec("echo 0 >/sys/class/gpio/gpio".GVAR::$RUNNING_LED."/value");
}

if( checkIfMaster() ) {
	echo "Master input script loaded\n";
	require_once '/maasland_app/scripts/coapListenerMaster.php';
} else {
	echo "Slave input script loaded\n";
	require_once '/maasland_app/scripts/coapListener.php';
}



