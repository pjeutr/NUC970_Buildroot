#!/usr//bin/php
<?php

require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/limonade.php';
require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/lib/logic.slave.php';
require_once '/maasland_app/www/lib/logic.master.php';
require_once '/maasland_app/vendor/arrilot/dotenv-php/src/DotEnv.php';
//load models for used db methods
require_once '/maasland_app/www/lib/model.report.php';
require_once '/maasland_app/www/lib/model.user.php';
require_once '/maasland_app/www/lib/model.settings.php';
require_once '/maasland_app/www/lib/model.door.php';
require_once '/maasland_app/www/lib/model.controller.php';
require_once '/maasland_app/www/lib/model.timezone.php';

//configure and initialize gpio 
echo configureGPIO();

//Quit quickly if not master, listener check is also not possible
//TODO, don't start on slave so we don't need this (Run cron through React?)
if( !checkIfMaster() ) {
	mylog("Stop Cron, this controller is not master");
	exit();
}

//initialize database connection
configDB();

$now = new DateTime();
$actor = "Cron"; 
$action = "Systemcheck ";
//find door->timezone_id fields	 

//check if everything is alive
//if($now->format('H:i') == "2:00") { //every night at 2
if($now->format('i') == 45) { //every hour

	exec("ps -o pid,user,comm,stat,args | grep -i 'inputListener' | grep -v grep", $pids);
	// D Uninterruptible sleep (usually IO)
	// R Running or runnable (on run queue)
	// S Interruptible sleep (waiting for an event to complete)
	// T Stopped, either by a job control signal or because it is being traced.
	// W paging (not valid since the 2.6.xx kernel)
	// X dead (should never be seen)
	// Z Defunct ("zombie") process, terminated but not reaped by its parent.

	if(empty($pids)) {
		$action = "inputListener not running!";
	} else {
	    $action = "Systemcheck, inputListener OK. ".count($pids)." pids:".join(',', $pids);
	}

	//check if listener still running?
	saveReport($actor, $action);
}

$doors = find_doors();
foreach ($doors as $door) {
	mylog("Cron: Contoller=".$door->controller_id.":".$door->cname."  Door=".$door->enum.":".$door->id.":".$door->name." tz=".$door->timezone_id."\n");
	//
	if( $door->timezone_id ) {
		if(checkDoorSchedule($door)) {
			$changed = operateDoor($door, 1);
			$action = "Scheduled ".$door->name." opened";
			if($changed) saveReport($actor, $action);
		} else {
			$changed = operateDoor($door, 0);
			$action = "Scheduled  ".$door->name." closed";
			if($changed) saveReport($actor, $action);
		}
		mylog($action);
	}
}


	



