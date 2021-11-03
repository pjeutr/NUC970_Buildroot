<?php
//TODO get timezone from settings in db
//date_default_timezone_set('Europe/Amsterdam');
//date_default_timezone_set('Europe/London');
//date_default_timezone_set('Australia/Sydney');
$tz = "Europe/Amsterdam";
date_default_timezone_set($tz);

//Custom log
function mylog($message) {
    //add milliseconds timestamp for 'permance' profiling
    $message = mdate('H:i:s-u')."_".$message;
    if(true) {
    //if(option('debug') && option('env') > ENV_PRODUCTION) {
        if(php_sapi_name() === 'cli') {
            echo($message);
        }
        return error_log($message);
    }
    return null;
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
    mylog($user." ".$msg."\n");

    //create report record in db
    $report = make_report_obj([
        "user"  => $user,
        "keycode"  => $key,
        "door" => $msg
    ]);
    return create_object($report, 'reports', null);
}


function apiCall($host, $uri) {
    $msg = "dummy";
    if(true) {
        $url = "http://".$host."/?/api/".$uri;
        mylog("apiCall:".$url);
        //$msg = file_get_contents($url);

        //TODO this is the 3rd place where a loop is created. "bad design?"
        $loop = React\EventLoop\Factory::create();
        $client = new React\HttpClient\Client( $loop );
        $request = $client->request('GET', $url);
        $request->on('response', function ( $response ) {
            $response->on('data', function ( $data ) {
                mylog("apiCall return=".json_encode($data));
                return $data;
            });
        });
        $request->end();
        $loop->run();
    } else {
        $cmd = "coap-client -m get coap://".$host."/".$uri;
        mylog("coapCall:".$cmd);
        //TODO hangs here shell_exec alternative
        $msg = shell_exec($cmd);//." 2>/dev/null &");
        mylog("shell_exec returns");
    }
    return $msg;

/*
$client = new GuzzleHttp\Client();
$res = $client->get('https://api.github.com/user', [
    'auth' =>  ['user', 'pass']
]);
mylog($res->getStatusCode());           // 200
mylog($res->getHeader('content-type')); // 'application/json; charset=utf8'
mylog($res->getBody());                 // {"type":"User"...'
mylog($res->json());             // Outputs the JSON decoded data
*/    
    
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
    onclick=\"app.areYouSure(this);return false;\"
    ><i class=\"fa fa-times\"></i>$name</a>";  

    // return "<a rel=\"tooltip\" title=\"$name\" class=\"btn btn-danger btn-link\" href=\"$url\"
    // onclick=\"if (confirm('Are you sure?')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'DELETE'); f.appendChild(m); f.submit(); };return false;\"
    // ><i class=\"fa fa-times\"></i>$name</a>";    
}

function print_date($timestamp) {
    //return $timestamp;
    $dt = new DateTime($timestamp);
    //Theorie, sqlite timestamp wordt UTC opgeslagen. Alles in php is met date_default_timezone_set gezet bovenaan ^
    //Daarom moet voor display van sqlite gezette datums worden gecorigeerd Amsterdam +0200 => +0400
    $dt->setTimezone(new DateTimeZone('+0400'));
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
