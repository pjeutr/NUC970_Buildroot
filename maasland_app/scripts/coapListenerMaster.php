<?php

/*
* coapServer & Listener
*
* Runs on master controller
* - Detects input changes (readers/buttons/sensors) and calls them locally
* - Listen for input changes on slaves (input)
*/

require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/logic.master.php';
//load models for used db methods
require_once '/maasland_app/www/lib/model.report.php';
require_once '/maasland_app/www/lib/model.user.php';
require_once '/maasland_app/www/lib/model.ledger.php';
require_once '/maasland_app/www/lib/model.settings.php';
require_once '/maasland_app/www/lib/model.door.php';
require_once '/maasland_app/www/lib/model.controller.php';
require_once '/maasland_app/www/lib/model.timezone.php';
require_once '/maasland_app/www/lib/model.rule.php';

//initialize database connection
configDB();

//anounce as master server
$r = mdnsPublish();
mylog("mdnsPublish return=".json_encode($r));

/*
* Listen for input changes (inputListener)
*/
//TODO class meegeven werkte niet, daarom maar de index van array
$wiegandObserver = new \Pjeutr\PhpNotify\Observer(0);
$wiegandObserver->onModify(function($file_name) {
	//find the value
	$value = getInputValue($file_name);
	$parts = explode(':',$value);
	$nr = $parts[0];
	$keycode = $parts[1];
	$reader = $parts[2];
	mylog("Wiegand:". $reader.":".$keycode);
	//$result =  callApi($reader, $keycode);
	$result = handleInput("127.0.0.1", $reader, $keycode);
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
		//$result =  callApi($input, "");
		$result = handleInput("127.0.0.1", $input, "");
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
* Run coapListener, listens for key/button changes from the slave
*/

$server = new PhpCoap\Server\Server();
$server->receive( 5683, '0.0.0.0' );

$server->on( 'request', function( $req, $res, $handler ) {
	$o = $req->GetOptions();
	$url = basename(readOption($o,0));
	mylog("url=".$url);
	// $path = explode('_',$url);
	// $type = $path[0];
	// $input = $path[1];
	// $keycode = $path[2];
	// does the same as above, but also checks if 1 and 2 are empty and replaces them with o
	list($type, $input, $keycode) = explode("_", $url . '_0_0');
	$result = 'dummy';
	mylog($type." input=".$input." keycode=".$keycode);

	switch ($type) {
	    case 'input':
	    case 'x':
	        $from = $handler->getPeerHost();
			mylog("coapServer: input=".$input." data=".$keycode);
			$result = handleInput($from, $input, $keycode);
			mylog("coapServer result=".json($result));
	        break;
	    case 'output':
	    case 'activate':
	    case 'status':
	        $result = $type.": is not available on the Master controller!";
	        break;
	    case 'dump':
			meminfo_dump(fopen("/tmp/dump$input.json", 'w'));
			$result = "dump$input";
			break;
	    default:
	    	$result = "invalid request";
	}

	$res->setPayload( json_encode( $result ) );
	$handler->send( $res );
});



/*
* Do cronlike stuff, previously done by crontab
*/
$interval = 60;
$timer = React\EventLoop\Loop::addPeriodicTimer($interval, function () {
	$now = new DateTime();
	$actor = "Scheduled"; 
	$action = "Systemcheck ";
	
	/*
	* Check reports and delete old ones and vacuum. (previously done by crontab)
	*/
	if($now->format('H:i') == "04:00") { //every night at 2, needs timezone adjustment so 4
	//if($now->format('i') == 45) { //every hour
		//delete rows older than x days in reports
		$days = 7;
		$action = cleanupReports($days);
		mylog($action);
		if($action > 0) {
			saveReport($actor, "Older than $days days. $action rows deleted in reports.");
		}
	}

	/*
	* Check if there are doors scheduled to open. (previously done by crontab)
	*/
	$doors = find_doors();
	$promises = [];

	foreach ($doors as $door) {
		mylog("Cron: Contoller=".$door->controller_id.":".$door->cname."  Door=".$door->enum.":".$door->id.":".$door->name." tz=".$door->timezone_id);

		//has this door a timezone assigned?
		if( $door->timezone_id ) {
			//check if the door needs to be open or close
			$open = checkDoorSchedule($door) ? 1 : 0;

			//send required state to the door
			$promises[] = operateDoor($door, $open)->then(
		        function ($value) {
					// Deferred resolved, do something with $value
					mylog("Promise return=".$value);
					return $value;
		        }
		    );
		}
	}
});
