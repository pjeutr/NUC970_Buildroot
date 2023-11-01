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

    //default message if code breaks out
    $swalMessage = swal_message("Something went wrong!");

    if($type < 9) { 
        //save setting to db, but not for 9 = system time 
        if(update_with_sql($sql, [$value,$id,$name])) {
            $swalMessage = swal_message("The Setting was changed!", "Great", "success");
        }
    }
    if($type == 4) { //hostname 
        $name = updateHostname($value);
        $swalMessage = swal_message("Hostname was changed to $name", "Great", "success");
    } 

    if($type == 9) { //system date time 
        //create DateTime from string
        $dt = DateTime::createFromFormat(getDateTimeFormat(), $value, new DateTimeZone(getTimezone() ) );
        //convert local time to utc
        $dt->setTimezone(new DateTimeZone('UTC'));
        //convert to compatible string for system 
        $timesStamp = $dt->format('Y-m-d H:i');
        //update hardware clock
        exec('hwclock --set --date="'.$timesStamp.'"', $out, $status); 
        mylog($status);
        mylog('hwclock --set --date="'.$timesStamp.'"');
        mylog(json_encode($out));
        if($status) { //error s=63
            $swalMessage = swal_message("Date Time has not changed", "Error", "error");
        } else { //ok is 0
            $swalMessage = swal_message("Date Time was changed", "Great", "success");
            //update system clock
            $r = shell_exec('hwclock -s'); 
            mylog($r);
            mylog("system clock updated");
        }
    } 

    set('swalMessage', $swalMessage);
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

function settings_download() {
    $fileUrl = '/maasland_app/www/db/prod.db';
    $fileName = "settings_".date("Y-m-d_H:i:s").".flexess";
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"" . basename($fileName) . "\""); 
    readfile($fileUrl); 
}

function settings_upload() {
    $target_dir = "/maasland_app/www/db/";
    //$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . "prod.db";
    $uploadOk = 1;
    //$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $imageFileType = "";
    $messageArr = array();
    $uploadOk = 0;

    // Check if file is present
    if(!empty($_FILES["fileToUpload"]["tmp_name"])) {
        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
          mylog($_FILES["fileToUpload"]["tmp_name"]);
          $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
          mylog("check=".json_encode($check));
          if($check !== false) {
            $messageArr[] = "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
          } else {
            //$messageArr[] = "File is not an image.";
            $uploadOk = 1;
          }
        }

        // Check if file already exists
        // if (file_exists($target_file)) {
        //   $messageArr[] = "File already exists.";
        //   $uploadOk = 0;
        // }

        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 7*1024*1024) { //7M, max in php.ini is 8M
          $messageArr[] = "The file is too large.";
          $uploadOk = 0;
        }

        // Allow certain file formats
        $imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"],PATHINFO_EXTENSION));
        mylog("imageFileType=".$imageFileType);
        if($imageFileType != "db" && $imageFileType != "flexess") {
          $messageArr[]= "Only flexess files are allowed.";
          $uploadOk = 0;
        }
    } else {
        $messageArr[]= "You have to choose a file.";
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $messageArr[]= "Your file was not uploaded.";

    //TODO weg?
    set('swalMessage', swal_message_error($messageArr));

    // if everything is ok, try to upload file
    } else {
      if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $messageArr[] = "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.<p>Wait 10 seconds, than restart the master controller</p>";
        //mylog(shell_exec("ls -la $target_file"));
        set('swalMessage', swal_message_success($messageArr));
      } else {
        $messageArr[] = "Sorry, there was an error uploading your file.";
        set('swalMessage', swal_message_error($messageArr));
      }
    }
    mylog($messageArr);
    
    set('settings', find_settings());
    return html('settings.html.php');
}