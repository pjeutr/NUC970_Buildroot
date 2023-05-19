#!/usr/bin/php
<?php
//exit;

// Too prevent multiple instances of this file, we use a lock file
$lock_file = fopen('/var/run/flexess_cron.pid', 'c');
$got_lock = flock($lock_file, LOCK_EX | LOCK_NB, $wouldblock);
if ($lock_file === false || (!$got_lock && !$wouldblock)) {
    throw new Exception(
        "Unexpected error opening or locking lock file. Perhaps you " .
        "don't  have permission to write to the lock file or its " .
        "containing directory?"
    );
}
else if (!$got_lock && $wouldblock) {
	error_log("WARNING:CRON ALREADY RUNNING! PID=".getmypid());
    exit("Another instance is already running; terminating.\n");
}

// Lock acquired; let's write our PID to the lock file for the convenience
// of humans who may wish to terminate the script.
ftruncate($lock_file, 0);
fwrite($lock_file, getmypid() . "\n");

//ps -a | grep [i]nputListener\.php && echo "Running" || echo "Not running"
$lstnr = shell_exec("ps -a | grep [i]nputListener\.php");
if (empty($lstnr)) {
	error_log("WARNING:INPUTLISTENER NOT RUNNING!");
	$startLstnr = shell_exec("/etc/init.d/S60flexess restart");
	//error_log($startLstnr);
}

//check and log cpu load, give Warning if it is getting big
$str = substr(strrchr(shell_exec("uptime"),":"),1);
$load = array_map("trim",explode(",",$str));
if ($load[0] > 0.80) {
	error_log("WARNING:HEAVY LOAD!");
}
//mylog("ld=".$load[0]);







