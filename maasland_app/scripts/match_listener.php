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

// php -f /maasland_app/scripts/match_listener.php >> /var/log/match_listener.log &

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
    $new_contents = file_get_contents($filename);

    //is some button pressed?
    $action = checkAndHandleInputs();

    if (strcmp($new_contents, $current_contents)) {
		$current_contents = $new_contents;
		echo "Activity nr:key:reader:raw:switch ".$new_contents;

		$content = explode(":",$new_contents);
		$nr = $content[0];
		$keycode = $content[1];
		$reader = $content[2];
		$raw = $content[3];

		$actor = $keycode;
		$action = "Reader ".$reader;

		//get User for the key
		$user = find_user_by_keycode($keycode);
		if($user) {
			$actor = $user->name;
			$action = handleUserAccess($user,$reader);
		} 
		
		//save report
		saveReport($actor, $action);

		//wait half a second, to avoid too much load on CPU
		usleep(500000);
    }
}


