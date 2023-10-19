#!/usr//bin/php
<?php

require_once '/maasland_app/www/lib/limonade.php';;
require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/lib/logic.door.php';
//load models for used db methods
require_once '/maasland_app/www/lib/model.report.php';
require_once '/maasland_app/www/lib/model.user.php';
require_once '/maasland_app/www/lib/model.settings.php';
require_once '/maasland_app/www/lib/model.door.php';
require_once '/maasland_app/www/lib/model.controller.php';
require_once '/maasland_app/www/lib/model.rule.php';
require_once '/maasland_app/www/lib/model.timezone.php';

//initialize database connection
$dsn = "sqlite:/maasland_app/www/db/dev.db";
$db = new PDO($dsn);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
option('dsn', $dsn);
option('db_conn', $db);
option('debug', true);

$r = setupGPIOInputs();
echo "setupGPIOInputs=".$r;

$filename = "/sys/kernel/wiegand/read";
$current_contents = "";  

while(true) {
	global $current_contents;
    $rawContents = file_get_contents($filename);
    //dissect the input
	$content = explode(":",$rawContents);
	$nr = $content[0];
	$keycode = $content[1];
	$reader = $content[2];
	$raw = $content[3];

    //is some button pressed?
    $action = checkAndHandleInputs();

    if (strcmp($nr, $oldNr)) {
		$oldNr = $nr;
		echo "Activity nr:key:reader:raw:switch ".$nr.":".$keycode.":".$reader."\n";

		$actor = "unkown";
		$action = "Reader ".$reader;

		//get User for the key
		$user = find_user_by_keycode($keycode);
		if($user) {
			$actor = $user->name;
			$action = handleUserAccess($user,$reader);
		} 
		
		//save report
		saveReport($actor, $action, keyToHex($keycode));

		//wait half a second, to avoid too much load on CPU
		usleep(500000);
    }
}


