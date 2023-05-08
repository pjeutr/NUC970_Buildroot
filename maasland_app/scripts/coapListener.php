<?php

/*
* coapServer & Listener
*
* Runs on slave controller
* - Detects input changes (readers/buttons/sensors) and sends them to the Master
* - Listen for commands from the master (activate/output/status)
*/

/* 
* Outgoing calls to master
*/
function callApi($input, $data) {
	//coap-client -m get coap://192.168.178.179/x/3
    $url = "coap://".getMasterControllerIP()."/x_".$input."_".$data;
    mylog("coapCall:".$url);
    //request
	$client = new PhpCoap\Client\Client();
	$client->get($url, function( $data ) {
		if($data == -1) {
            error_log("coapCall, Master controller could not be reached.");
            //Sound 4 beeps on the slave controller to warn the user.
            beepMessageBuzzer(2);
        } else {
            mylog("coapCall, return=".json_encode($data));
        }
        return $data;
	});
}

/*
* Listen for input changes (inputListener)
*/
//TODO class meegeven werkte niet, daarom maar de index van array
$wiegandObserver = new \Pjeutr\PhpNotify\Observer(0);
$wiegandObserver->onModify(function($file_name){
	//find the value
	$value = getInputValue($file_name);
	$parts = explode(':',$value);
	$nr = $parts[0];
	$keycode = $parts[1];
	$reader = $parts[2];
	mylog("Wiegand:". $reader.":".$keycode);
	$result = callApi($reader, $keycode);
    mylog(json_encode($result));
});	

$lastMicrotime = 0;
$inputObserver = new \Pjeutr\PhpNotify\Observer(1);
$inputObserver->onModify(function($file_name) use ($lastMicrotime){
	global $lastMicrotime;

	mylog("Modified:". $file_name. "\n");
	//determine the input number for this file
	$input = resolveInput($file_name);
	//find the value
	$value = getInputValue($file_name);
	//mylog("value:". $value. "\n");
	//take action if a button is pressed
	if($value == 1) { 
		//Debounce a swich 200ms would be normal, but since there's a open/close reaction almost a second is also fine
		$microtimeNow = microtime(true);
		mylog("microtimeNow:". $microtimeNow."-".$lastMicrotime."=".($microtimeNow - $lastMicrotime));
		if ($microtimeNow - $lastMicrotime < .9) {
			mylog("DEBOUNCE ACTIVE");
			return;
		}
		$lastMicrotime = $microtimeNow;

		mylog("Button:". $input);
		$result =  callApi($input, $value); //$value is always 1...
        mylog(json_encode($result));
	}   
});

//listen voor gpio inputs
$inputArray = getInputArray();
foreach ($inputArray as $value) {
	mylog("inputObserver init:". $value ." \n");
    $inputObserver->watch($value);
}
//$inputObserver->watch('/sys/class/gpio/gpio170/value');

//listen voor wiegand readers
$wiegandObserver->watch('/dev/wiegand');


/*
* Run coapListener, listens for commands from the master, open door/sound alarm
*/
$server = new PhpCoap\Server\Server();
$server->receive( 5683, '0.0.0.0' );

$server->on( 'request', function( $req, $res, $handler ) {
	$o = $req->GetOptions();
	$url = basename(readOption($o,0));
	mylog("url=".$url);

	$from = $handler->getPeerHost(); //will allways be master

	//Security check if it's really the master
	// if(getMasterControllerIP() != $from) {
	// 	//TODO recheck or don't check...
	// 	error_log("WARNING: Coap request from=".$from." master=".getMasterControllerIP());
	// 	$res->setPayload( json_encode( "illegal encryption key" ) );//confuse the hacker ;)
	// 	$handler->send( $res );
	// }

	// $path = explode('_',$url);
	// $type = $path[0];
	// $output = $path[1];
	// $param = $path[2];
	// $gpios = $path[3];
	// does the same as above, but also checks if 1,2 and 3 are empty and replaces them with 0
	list($type, $output, $param, $gpios) = explode("_", $url . '_0_0_0');
	$result = 'dummy';

	switch ($type) {
	    case 'input':
	    case 'x':
	        $result = $type.": is not available on a Slave controller!";
	        break;
	    case 'output':
			mylog("coapListener: Open door ".$output." state=".$param."s gpios=".json_encode($gpios));
			$result = operateOutput($output, $param, explode("-", $gpios));
			break;
	    case 'activate':
			mylog("coapListener: Activate door ".$output." for ".$param."s gpios=".json_encode($gpios));
			$result = activateOutput($output, $param, explode("-", $gpios));
			break;
		case 'value':
	    	$result = getGPIO($output);
	    	mylog("coapListener: Value gpio=".$output."=".$result);
	        break;
	    case 'status':
			$gpios = explode("-", $output);
			mylog("coapListener: Status gpios=".json_encode($gpios));
			$result = array();
			foreach ($gpios as $gpio) {
	        	$result[] = array($gpio => getGPIO($gpio));
	    	}
	        break;
	    default:
	    	$result = "invalid request";
	}

	$res->setPayload( json_encode( $result ) );
	$handler->send( $res );
});


