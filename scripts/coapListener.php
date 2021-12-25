#!/usr/bin/php
<?php

/*
* coapServer & Listener
*
* Runs on all controllers
* - Detects input changes (readers/buttons/sensors) and sends them to the Master
* - Listen for commands from the master (activate/output/status)
*/

/* 
* Outgoing calls to master
*/
function callApi($input, $data) {
	if(false) {
    	$url = "http://".getMasterControllerIP()."/?/api/input/".$input."/".$data;
        mylog("apiCall:".$url);
        $loop = React\EventLoop\Loop::get();
		$client = new React\HttpClient\Client( $loop );
		$request = $client->request('GET', $url);
		$request->on('response', function ( $response ) {
		    $response->on('data', function ( $data ) {
		    	mylog("apiCall return=".json_encode($data));
		        return $data;
		    });
		});
		$request->end();

	} else {
		//coap-client -m get coap://192.168.178.179/x/3
		//40 01 x x b1 78 - 01 33 ---- 40 01 77 91 b1 78 01 33
		//{"1":64,"2":1,"3":x,"4":x,"5":177,"6":120,"7":1,"8":51}
		//[64,1,x,x,177,120,1,51,0] = -3 = goed
		//[64,1,x,x,180,120,1,51,0] = nix

		//for now but bogus behind
		//coap-client -m get coap://192.168.178.179/x/3/6666
		//40 01 x x b1 78 - 01 33 - 04 36 36 36 36 --- 40 01 c6
		//[64,1,x,x,177,120, - 1,51,- 4,54,54,54,54] = -7
		//[64,1,x,x,184,120, - 1,51,- 4,54,54,54,54] = nix

		//-5 coap-client -m get coap://192.168.178.179/x/1/3333
		//40 01 x x b1 78 - 01 31 - 04 33 33 33 33 ------ 40 01 97

		//-7 coap-client -m get coap://192.168.178.179/x/1/2310811
		//40 01 x x b1 78 - 01 31 - 07 32 33 31 30 38 31 31
		//[64,1,x,x,177,120,1,50,7,50,51,49,48,56,49,49] = -10
		//[64,1,x,x,180,120,1,50,7,50,51,49,48,56,49,49] = -7



		//coap-client -m get coap://192.168.178.118/input/1/3333
		// 40 01 - 43 b7 b5 - 69 6e 70 75 74 - 01 31 - 04 33 33 33 33 - 40
		// coap-client -m get coap://192.168.178.118/input/1/2310811
		// 40 01 - 17 95 b5 - 69 6e 70 75 74 - 01 31 - 07 32 33 31 30 38 31 31

		//coap-client -m get coap://192.168.178.118/x/1/3333

		// coap-client -m get coap://192.168.178.118/x/1/
		// 40 01 x x - b1 78 - 01 31 - 07 32 33 31 30 38 31 31
		//{"1":64,"2":1,"3":127,"4":211,"5":177,"6":120,"7":1,"8":51}

		// coap-client -m get coap://192.168.178.118/x/1/333
		// 40 01 b9 ee - b1 78 - 01 31 - 03 33 33 33 
		// v1 get- x  x  b5 - input          - /1    - /3333 slash=count - end/begin 

		// 40 01 - 15 f0 (bd - 02) - 69 6e 70 75 74 2f 31 - 2f 32 33 31 
        //$url = "coap://192.168.178.118/x/".$input."/".$data;
        //$url = "coap://".getMasterControllerIP()."/x/".$input."/".$data;

		//$url = "coap://192.168.178.118/x_".$input."_".$data;
        $url = "coap://".getMasterControllerIP()."/x_".$input."_".$data;
        mylog("coapCall:".$url);
        //request
        $loop = React\EventLoop\Loop::get();
		$client = new PhpCoap\Client\Client( $loop );
		$client->get($url, function( $data ) {
			mylog("coapCall return=".json_encode($data));
			return $data;
		});

		// $cmd = "coap-client -m get ".$url;
		// $output=null;
		// $retval=null;
		// exec($cmd, $output, $retval);
		// mylog("Returned with status $retval and output:");
		// mylog($output);

	}
       
}

/*
* Listen for input changes (inputListener)
*/
//TODO class meegeven werkte niet, daarom maar de index van array
//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, EpollWait::class);
$wiegandObserver = new \Pjeutr\PhpNotify\Observer($loop, 0);
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

//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, InotifyWait::class);
$inputObserver = new \Pjeutr\PhpNotify\Observer($loop, 1);
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
		//for now but bogus 6666 behind
		$result =  callApi($input, "");
        mylog(json_encode($result));
	}   
	//TODO sleep / prevent klapperen 
	//sleep(1);
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
* Run coapListener
*/
$server = new PhpCoap\Server\Server( $loop );
$server->receive( 5683, '0.0.0.0' );

$server->on( 'request', function( $req, $res, $handler ) {
	$o = $req->GetOptions();
	$url = basename(readOption($o,0));
	mylog("url=".$url);

	$from = $handler->getPeerHost(); //will allways be master
	//TODO security check if it's really the master?

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


