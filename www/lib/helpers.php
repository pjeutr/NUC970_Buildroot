<?php
class GVAR
{
    #define SENSER_PIN     NUC980_PB4   //senser input
    #define TAMPER_PIN     NUC980_PB13  //tamp input
    #define ARM_PIN        NUC980_PC0   //alarm input
    #define RELAY3_PIN     NUC980_PC1   //alarm relay output
    #define RELAY2_PIN     NUC980_PC2   //relay2 output
    #define SOUNDER_PIN    NUC980_PC3   //sounder output
    #define RELAY1_PIN     NUC980_PC4   //relay1 output
    public static $GPIO_DOOR1 = 68; //NUC980_PC4
    public static $GPIO_DOOR2 = 66; //NUC980_PC2
    public static $GPIO_ALARM1 = 65; //NUC980_PC2
    public static $DOOR_TIMER = 2; //Door lock stays open for 2s

    //$RD1_RLED_PIN = 3; //NUC980_PA3   //reader1 rled output
    public static $RD1_GLED_PIN = 2; //NUC980_PA2   //reader1 gled output
    //$RD2_RLED_PIN = 11; //NUC980_PA11  //reader2 rled output
    public static $RD2_GLED_PIN = 10;  //NUC980_PA10  //reader2 gled output
}

function handleUserAccess($user, $reader) {
    //update last seen field of user
    update_user_last_seen($user);
    $door = find_door_by_id($reader);

    //check if the group/user has access
    //TODO check door and timezone, from access record
    $msg = "Opened ". $door->name;

    //save report and open the door 
    saveReport($user->name, $msg);
    openDoor($reader);
    
    return true;    
}

function saveReport($name, $msg) {
    //create report entry in log
    mylog($name." ".$msg."\n");

    //create report record in db
    $report = make_report_obj([
        "user"  => $name,
        "door" => $msg
    ]);
    return create_object($report, 'reports', null);
}

function mylog($message) {
    if(php_sapi_name() === 'cli') {
        //TODO add timestamp
        echo($message);
    }
    return error_log($message);
}

function openDoor($reader) {
    //determine which reader is used, so we can select the proper led
    $gled = 0;
    $gid = 0;

    //determine the right door, assume reader1=door1, reader2=door2
    //TODO config reader2 to also open door 1?
    if($reader == 1) {
        $gled = GVAR::$RD1_GLED_PIN;
        $gid = GVAR::$GPIO_DOOR1;
    }
    if($reader == 2) {
        $gled = GVAR::$RD2_GLED_PIN;
        $gid = GVAR::$GPIO_DOOR2;
    }
    if($reader == 3) {
        $gid = GVAR::$GPIO_ALARM1;
    }
    mylog("Open Door GPIO=".$gid." reader=".$reader." LED=".$gled."\n");
    //open lock
    setGPIO($gid, 1);
    //turn on led
    if($gled) setGPIO($gled, 1);
    //wait some time. close lock
    sleep(GVAR::$DOOR_TIMER);
    //turn off led
    if($gled) setGPIO($gled, 0);
    return setGPIO($gid, 0);
}

function setGPIO($gid, $state) {
    mylog("setGPIO ".$gid."=".$state."\t");
    if(! file_exists("/sys/class/gpio/gpio".$gid)) {
        mylog("init gid=".$gid);
        shell_exec("echo ".$gid." > /sys/class/gpio/export");
        shell_exec("echo out >/sys/class/gpio/gpio".$gid."/direction");
    }
//led 40 & 45
//1 = off, 0 = on
//$gpio_on = shell_exec('gpio write 1 1');
// echo 40 > /sys/class/gpio/export
// echo out >/sys/class/gpio/gpio40/direction
// echo 1 >/sys/class/gpio/gpio40/value
//switch 90 & 92 not working propably needs a 4.7K pull-down resistor?
// cat /sys/class/gpio/gpio90/active_low    
// echo both > /sys/class/gpio/gpio90/edge
// rising / falling / both / none

    shell_exec("echo ".$state." >/sys/class/gpio/gpio".$gid."/value");
    
    return 1;    
}

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

function iconLink_to($name, $link, $style, $icon) {
	$url = url_for($link);
    $fa = isset($icon) ? "<i class=\"fa $icon\"></i>" : "";
    
    return "<a rel=\"tooltip\" title=\"$name\" class=\"btn btn-success $style\" href=\"$url\"><i class=\"fa fa-edit\"></i>$name</a>";    

    //return '<a href="#" rel="tooltip" title="Edit Profile" class="btn btn-success btn-link btn-xs"><i class="fa fa-edit"></i></a>';
    //return "<a class=\"btn $style\" href=\"$url\">$fa $name</a>";    
}

function deleteLink_to($params = null) {
	$params = func_get_args();
    $name = array_shift($params);
    $url = call_user_func_array('url_for', $params);
    
    //return '<a href="#" rel="tooltip" title="Remove" class="btn btn-danger btn-link"><i class="fa fa-times"></i></a>';

    return "<a rel=\"tooltip\" title=\"$name\" class=\"btn btn-danger btn-link\" href=\"$url\"
    onclick=\"app.areYouSure(this);return false;\"
    ><i class=\"fa fa-times\"></i>$name</a>";  

    // return "<a rel=\"tooltip\" title=\"$name\" class=\"btn btn-danger btn-link\" href=\"$url\"
    // onclick=\"if (confirm('Are you sure?')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'DELETE'); f.appendChild(m); f.submit(); };return false;\"
    // ><i class=\"fa fa-times\"></i>$name</a>";    
}

function option_tag($id, $title, $act_id) {
    $s = '<option value="' . $id . '"';
    $s .= ($id == $act_id) ? ' selected="true"' : '';
    $s .= '>' . $title . '</option>';
    return $s;
}

//Make object with empty values, from an array of names
function make_empty_obj($values) {
    $user_data = new stdClass();
    foreach ($values as $key => $value) {
        $user_data->$value = '';
    }
    return $user_data;
}

