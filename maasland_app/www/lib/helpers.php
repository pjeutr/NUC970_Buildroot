<?php
//TODO get timezone from settings in db
//date_default_timezone_set('Europe/Amsterdam');
//date_default_timezone_set('Europe/London');
//date_default_timezone_set('Australia/Sydney');
function getTimezone() {
    return "Europe/Amsterdam";
}
function getDateTimeFormat() {
    return 'd/m/Y H:i';
}
function getTimeFormat() {
    return 'H:i';
}
//Custom log
function mylog($message) {
    //add milliseconds timestamp for 'permance' profiling
    $message = mdate('H:i:s-u')."_".(is_string($message) ? $message : json_encode($message));

    //$debug = option('debug');
    //error_log("debug=".option('debug')." log_level=".option('log_level'));

    if( option('debug') ) {
    //if(option('debug') && option('env') > ENV_PRODUCTION) {
        // if(php_sapi_name() === 'cli') {
        //     echo($message."\n");
        // }
        return error_log($message);
    }
    return null;
}

//beep the buzzer, to let the user hear there is an error
//$speed/10 => 2 = .2s
function beepMessageBuzzer($speed) {
    mylog("beepMessageBuzzer $speed".PHP_EOL);

    $value = 1;
    $timer = React\EventLoop\Loop::addPeriodicTimer($speed/10, function () use (&$value) {
        $value = ($value==1 ? 0 : 1);
        exec("echo ".$value." >/sys/class/gpio/gpio".GVAR::$BUZZER_PIN."/value");
    });

    //turn buzzer off after 2 seconds
    React\EventLoop\Loop::addTimer(2.0, function () use ($timer) {
        React\EventLoop\Loop::cancelTimer($timer);
        //turn led off, too indicate something was wrong., off = 1
        exec("echo 0 >/sys/class/gpio/gpio".GVAR::$BUZZER_PIN."/value");
    });
}

//blink a led, to let the user see there is an error
//$speed/10 => 2 = .2s
function blinkMessageLed($speed) {
    mylog("blinkMessageLed $speed".PHP_EOL);

    $value = 1;
    $timer = React\EventLoop\Loop::addPeriodicTimer($speed/10, function () use (&$value) {
        $value = ($value==1 ? 0 : 1);
        exec("echo ".$value." >/sys/class/gpio/gpio".GVAR::$RUNNING_LED."/value");
    });

    //turn blinking off after 10 seconds
    React\EventLoop\Loop::addTimer(10.0, function () use ($timer) {
        React\EventLoop\Loop::cancelTimer($timer);
        //turn led off, too indicate something was wrong., off = 1
        exec("echo 1 >/sys/class/gpio/gpio".GVAR::$RUNNING_LED."/value");
    });
}

//Make object with empty values, from an array of names
function make_empty_obj($values) {
    $user_data = new stdClass();
    foreach ($values as $key => $value) {
        $user_data->$value = '';
    }
    return $user_data;
}

//Typed in code is max 999999
//if it is bigger, it's a tag and we need to translate to hex value
function keyToHex($key) {
    //mylog("keyToHex called key=".$key);
    if((int)$key > 9999) {
        $value = strtoupper(dechex((int)$key));
        //mylog("converted to ".$value);
        return $value;
        //return dec2hex($key);
    }
    return $key;
}

//Save a record to reports db
function saveReport($user, $msg, $key = "empty") { //empty => null
    //create report entry in log
    mylog("saveReport:".$user." ".$msg."\n");

    //create report record in db
    $report = make_report_obj([
        "user"  => $user,
        "keycode"  => $key,
        "door" => $msg
    ]);
    mylog("saveReport:Done");
    return create_object($report, 'reports', null);
}

/* 
* Outgoing calls to slave
*/
function apiCall($host, $uri) {
    $msg = "dummy";

    $url = "coap://".$host."/".str_replace('/','_',$uri);
    mylog("coapCall:".$url);
    //request
    $client = new PhpCoap\Client\Client();
    $client->get($url, function( $data ) {
        mylog("coapCall return=".$data);
        return $data;
    });        

    return $msg;    
}

/* 
*   mDNS/Avahi functions 
*/
$serviceTypeUdp = "_maasland._udp 5683"; //created by avahi deamon
$serviceTypeTcp = "_maasland._tcp 80"; //created by coap_listener (avahi publish)
$serviceMasterSubType = "_master._sub._maasland._udp"; //created by coap_listener (avahi publish)

//avahi-publish-service flexess _coap._udp 5683 "version=1.4" --sub _master._sub._maasland._udp
//avahi-publish-service flexess _maasland._tcp 80 "version=1.4" --sub _master._sub._maasland._udp
function mdnsPublish() {
    global $serviceTypeTcp, $serviceMasterSubType;
    $cmd = "avahi-publish-service ".
        'flexess '.$serviceTypeTcp.' "path=/" "version=1.4" '.
        "--sub ".$serviceMasterSubType;
    mylog($cmd."\n");
    return exec($cmd. " > /dev/null &");
}
function mdnsBrowse($type) {
    $cmd = "avahi-browse -trp ". $type;
    mylog($cmd."\n");
    $result = array();
    $lines = explode("\n", shell_exec($cmd));
    foreach ($lines as &$line) {
        if(strpos($line, '=') === 0) {
            $result[] = explode(";", $line);
        }     
    }
    //unset($result);
    return $result;
}

/* 
*   HTML View functions 
*/
function buttonLink_to($params = null) {
    $params = func_get_args();
    $name = array_shift($params);
    $url = call_user_func_array('url_for', $params);
    return "<a class=\"btn btn-secondary\" href=\"$url\">$name</a>";
}

function link_to($params = null) {
    $params = func_get_args();
    $name = array_shift($params);
    $url = call_user_func_array('url_for', $params);

    return "<a href=\"$url\">$name</a>";
}

function iconLink_to($name, $link, $style, $icon = null) {
	$url = url_for($link);
    $fa = isset($icon) ? "<i class=\"fa $icon\"></i>" : "<i class=\"fa fa-edit\"></i>";
    
    return "<a rel=\"tooltip\" title=\"$name\" class=\"btn btn-success $style\" href=\"$url\">$fa</i>$name</a>";    

    //return '<a href="#" rel="tooltip" title="Edit Profile" class="btn btn-success btn-link btn-xs"><i class="fa fa-edit"></i></a>';
    //return "<a class=\"btn $style\" href=\"$url\">$fa $name</a>";    
}

function deleteLink_to($params = null) {
	$params = func_get_args();
    $name = array_shift($params);
    $url = call_user_func_array('url_for', $params);
    
    //return '<a href="#" rel="tooltip" title="Remove" class="btn btn-danger btn-link"><i class="fa fa-times"></i></a>';

    return "<a rel=\"tooltip\" title=\"$name\" class=\"btn btn-danger btn-link text-danger\" href=\"$url\"
    onclick=\"app.areYouSure(this,'".L('delete_confirm')."','".L('delete_subtext')."');return false;\"
    ><i class=\"fa fa-times\"></i>$name</a>";  

    // return "<a rel=\"tooltip\" title=\"$name\" class=\"btn btn-danger btn-link\" href=\"$url\"
    // onclick=\"if (confirm('Are you sure?')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'DELETE'); f.appendChild(m); f.submit(); };return false;\"
    // ><i class=\"fa fa-times\"></i>$name</a>";    
}

//format message for swal
//TODO reorganise in app.js
function swal_message($message, $title="Oops", $type="error") {
    if(is_array($message)) {
        $message = implode("<br>", $message);
    } else $message;
    return "{type: '$type' ,title: '$title', html: '$message'}";
}
function swal_message_error($message) {
    return swal_message($message, L("message_error_title"), "error");
}
function swal_message_success($message) {
    return swal_message($message, L("message_success_title"), "success");
}

function print_date($timestamp) {
    //$tz = "Europe/Amsterdam";
    //https://stackoverflow.com/questions/20288789/php-date-with-timezone
    //date_default_timezone_set($tz); //bad practic volgen bovenstaande lL("message_changes_saved_title")ink
    //https://stackoverflow.com/questions/3792066/convert-utc-dates-to-local-time-in-php

    // create a $dt object with the UTC timezone
    $dt = new DateTime($timestamp, new DateTimeZone('UTC'));
    // change the timezone of the object without changing its time
    $dt->setTimezone(new DateTimeZone( getTimezone() )); 
    return $dt->format('d-m-Y H:i:s');
}

function option_tag($id, $title, $act_id) {
    $s = '<option value="' . $id . '"';
    $s .= ($id == $act_id) ? ' selected="true"' : '';
    $s .= '>' . $title . '</option>';
    return $s;
}

function mdate($format = 'u', $utimestamp = null) {
    if (is_null($utimestamp))
        $utimestamp = microtime(true);

    $timestamp = floor($utimestamp);
    //$milliseconds = round(($utimestamp - $timestamp) * 1000000);
    $milliseconds = round(($utimestamp - $timestamp) * 1000);

    return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
}

function weekDaysPlus($stringArray) {
    $pieces = explode(",", $stringArray);
    $result = array_map(function($n){return $n+1;}, $pieces);
    return implode(",",$result);
}

/* 
*   cli functions - only used by cli 
*/
//configDB, keep it the same as in configure index.php
function configDB() {
    $development = Arrilot\DotEnv\DotEnv::get('APP_DEVELOPMENT', false);

    mylog("Env debug=".option('debug')." development=".$development);

    $env = $development ? ENV_DEVELOPMENT : ENV_PRODUCTION;
    $dsn = $env == ENV_PRODUCTION ? 'sqlite:/maasland_app/www/db/prod.db' : 'sqlite:/maasland_app/www/db/dev.db';
    mylog(json_encode($dsn));

    $db = new PDO($dsn);
    //$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

    /* DB in Memory 
    -- needs more work, db is not persistent, 
    - a change initiates db clone
    - 
    $db = new PDO('sqlite::memory:');
    //$db = new PDO($dsn, null, null, array(PDO::ATTR_PERSISTENT => true));
    //$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

    $sql = "/maasland_app/www/db/schema.sql";
    try {
      $db->exec(file_get_contents($sql));
      $db->query("/maasland_app/www/db/data.sql");
    } catch (Exception $e) {
        var_dump($e);
        exit;
    }
    */

    option('env', $env);
    option('dsn', $dsn);
    option('db_conn', $db);   
}

/*
* Helper to read options from the request (ReactPHP specific)
* to prevent errors, check if an option exist before reading the value
*/  
function readOption($option, $i) {
    if (array_key_exists($i, $option)) {
        return $option[$i] ? $option[$i]->getValue() : null;
    }
    return null;
}

