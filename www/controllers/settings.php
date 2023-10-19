<?php
/*
ideas copied from, more webgui->system congig stuff over there (fi timezones)
https://git.ispconfig.org/ispconfig/ispconfig3
ispconfig3/server/plugins-available/network_settings_plugin.inc.php

template used:
http://vlib.clausvb.de/docs/multihtml/vlibtemplate/tutorial_simple_example.html

*/
define('ISPC_CLASS_PATH', 'lib/vlibtemplate');
require "lib/vlibtemplate/tpl.inc.php";

function settings_index() {
    set('settings', find_settings());
    return html('settings.html.php');
}
function settings_update() {
    $id = filter_var(params('id'), FILTER_VALIDATE_INT);
    $type = filter_var($_POST['setting_type'], FILTER_SANITIZE_STRING);
    $name = filter_var($_POST['setting_name'], FILTER_SANITIZE_STRING);

    if($type == 2) { //checkbox 
        $value = isset($_POST[$name])?1:0;
    } else {
        $value = filter_var($_POST[$name], FILTER_SANITIZE_STRING);
    }
    
    //name and id are both unique, we could use only one of those.
    $sql = "UPDATE settings SET value = ? WHERE id = ? AND name = ?";
    mylog("A setting was changed ".$id.":".$name."=".$value);
    
    $message = "{type: 'error' ,title: 'Oops', text: 'Something went wrong!'}";
    if(update_with_sql($sql, [$value,$id,$name])) {
        $message = "{type: 'success' ,title: 'Great', text: 'The Setting was changed!'}";
    }

    if($type == 4) { //hostname 
        $name = updateHostname($value);
        $message = "{type: 'success' ,title: 'Great', text: 'Hostname was changed to ".$name."'}";
    } 

    set('message', $message);
    set('settings', find_settings());
    return html('settings.html.php');
}

function updateHostname($hostname) {
    $output=null;
    $retval=null;

    //make backup
    copy('/etc/hostname', '/etc/hostname~');

    //create new file
    file_put_contents('/etc/hostname',$hostname);

    shell_exec('hostname -F /etc/hostname');
    //$result = shell_exec('hostname');
    exec('hostname', $output, $retval);
    $hostname = $output[0];

    //restart network to load changes
    exec('/etc/init.d/S40network restart');

    //return $retval. "-" . json_encode($output);
    return $hostname;
}


function updateNetwork($hostname) {
    //make backup
    copy('/etc/network/interfaces', '/etc/network/interfaces~');

    //create template with changes
    $network_tpl = new tpl();
    $network_tpl->newTemplate('/maasland_app/www/views/layout/network_interfaces.tpl');

    $network_tpl->setVar('ip_address', "666");
    $network_tpl->setVar('netmask', "666");
    $network_tpl->setVar('gateway', "666");
    //$network_tpl->setVar('broadcast', $this->broadcast($server_config['ip_address'], $server_config['netmask']));
    //$network_tpl->setVar('network', $this->network($server_config['ip_address'], $server_config['netmask']));

    file_put_contents('/etc/network/interfaces', $network_tpl->grab());
    unset($network_tpl);

    //restart network to load changes
    //exec('/etc/init.d/S40network restart');
}

