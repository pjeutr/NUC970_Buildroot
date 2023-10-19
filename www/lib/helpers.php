<?php
//TODO get timezone from settings in db
//date_default_timezone_set('Europe/Amsterdam');
//date_default_timezone_set('Europe/London');
//date_default_timezone_set('Australia/Sydney');
$tz = "Europe/Amsterdam";
date_default_timezone_set($tz);

//Custom log
function mylog($message) {
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
    mylog("keyToHex called key=".$key);
    if((int)$key > 9999) {
        $value = strtoupper(dechex((int)$key));
        mylog("converted to ".value);
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

/* 
    View functions 
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

