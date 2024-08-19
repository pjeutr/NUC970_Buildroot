<?php
/*
ideas copied from, more webgui->system congig stuff over there (fi timezones)
https://git.ispconfig.org/ispconfig/ispconfig3
ispconfig3/server/plugins-available/network_settings_plugin.inc.php

template used:
http://vlib.clausvb.de/docs/multihtml/vlibtemplate/tutorial_simple_example.html

*/

function network_index() {
    set('network', getNetworkData());
    return html('network.html.php');
}
function network_slave() {
    set('network', getNetworkData());
    return html('network_slave.html.php');
}

/*
*   function can be called from network.html.php and network_slave.html.php
*   id 1 => change network on master => network.html.php
*   id 2 => change network slave => network_slave.html.php
*   id 3 => master ip => network_slave.html.php
*/
function network_update() {
    $id = filter_var(params('id'), FILTER_VALIDATE_INT);
    //standard message is failure, update to success if something has changed
    $swalMessage = swal_message("Something went wrong!");
    //$restartMessage = L::message_change_needs_reset;
    $restartMessage = "<p>Wait 10s, and restart the system to load new settings</p>";

    if($id == 3) { 
        if(isset($_POST['master'])) {
            updateMasterIP(false);

            if(update_with_sql("UPDATE settings SET value = 1 WHERE id = 10 AND name = 'master_ip'", [])) {
                //$swalMessage = swal_message("Automatic Master IP discovery enabled".$restartMessage, "Great", "countdown");
                $swalMessage = swal_message_countdown("Automatic Master IP discovery enabled".$restartMessage, 10000);
            }
        } else {
            $ip = filter_var($_POST['master_ip'], FILTER_SANITIZE_STRING);
            $reloadService = "To load changes, the controller needs to be restarted";
            updateMasterIP($ip);

            if(update_with_sql("UPDATE settings SET value = '$ip' WHERE id = 10 AND name = 'master_ip'", [])) {
                //$swalMessage = swal_message("Automatic Master IP discovery disabled, <br>Master IP has changed to :".$ip.$restartMessage, "Great", "countdown");
                $swalMessage = swal_message_countdown("Automatic Master IP discovery disabled, <br>Master IP has changed to :".$ip.$restartMessage, 10000);
            }
        }
    } else {
        //change network
        if(isset($_POST['dhcp'])) {
            mylog("DHCP");

            updateNetworkMakeDHCP();

            if(update_with_sql("UPDATE settings SET value = 1 WHERE id = 11 AND name = 'dhcp'", [])) {
                //$swalMessage = swal_message("Network settings have changed to DHCP!".$restartMessage, "Great", "countdown");
                $swalMessage = swal_message_countdown("Network settings have changed to DHCP!".$restartMessage, 10000);
            }
        } else {
            $ip = filter_var($_POST['ip'], FILTER_SANITIZE_STRING);
            $subnet = filter_var($_POST['subnet'], FILTER_SANITIZE_STRING);
            $router = filter_var($_POST['router'], FILTER_SANITIZE_STRING);

            updateNetwork($ip, $subnet, $router);

            $result = "Network settings have changed to static<br> IP : ".$ip."<br> Subnet Mask : ".$subnet."<br> Router : ".$router;
            mylog($result);

            if(update_with_sql("UPDATE settings SET value = 0 WHERE id = 11 AND name = 'dhcp'", [])) {
                //$swalMessage = swal_message($result.$restartMessage, "Great", "countdown");
                $swalMessage = swal_message_countdown($result.$restartMessage, 10000);
            }
        }
    }
    set('swalMessage', $swalMessage);
    set('network', getNetworkData());
    if($id == 1) { 
        return html('network.html.php');
    } 
    return html('network_slave.html.php');
}

/*
* get Data for the network page in settings on the master controller
* and the manage/network page for the slave controller, master data is only use for the slave controller
*/
function getNetworkData() {
    $master = find_setting_by_id(10); //id 10 is dhcp in db
    $master_ip = getMasterControllerIP();
    $dhcp = find_setting_by_id(11); //id 11 is dhcp in db

    $ip = $_SERVER['SERVER_ADDR'];
    //ifconfig eth0 | awk -F: '/Mask:/{print $4}'
    //ifconfig eth0 | sed -rn '2s/ .*:(.*)$/\1/p'
    $subnet = exec("ifconfig eth0 | awk -F: '/Mask:/{print $4}'");
    //ip route show | sed 's/\(\S\+\s\+\)\?default via \(\S\+\).*/\2/p; d'
    //route -n | grep "^0\.0\.0\.0" | awk '{print $2}'
    $router = exec("route -n | grep '^0\.0\.0\.0' | awk '{print $2}'");
    mylog($router);

    return array(
        "dhcp" => empty($dhcp) ? false : $dhcp,
        "master" => is_numeric($master) ? true : false, //ip in db, is only used as indicator, ip in file is leading
        "master_ip" => $master_ip,
        "ip" => $ip,
        "subnet" => $subnet,
        "router" => $router,
    );
}

function settings_index() {
    set('settings', find_settings());
    return html('settings.html.php');
}
function settings_update() {
    $id = filter_var(params('id'), FILTER_VALIDATE_INT);
    $type = filter_var($_POST['setting_type'], FILTER_SANITIZE_STRING);
    $name = filter_var($_POST['setting_name'], FILTER_SANITIZE_STRING);

    if($type == 7) { //checkbox 
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
        //save setting to db, but not for 
        //9 = system time 
        //10 = master ip
        //11 = dhcp
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
        if($dt = DateTime::createFromFormat(getDateTimeFormat(), $value, new DateTimeZone(getMyTimezone() ) )) {
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
        } else {
            $swalMessage = swal_message("Not a valid time format!");
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
    //exec('/etc/init.d/S40network restart');

    //return $retval. "-" . json_encode($output);
    return $hostname;
}

/*
*   updateMasterIP - if master IP is set, a slave will use this adress, 
*   instead of automatically searching for one by mDNS, this is only needed when a network doesn't allow multicast
*   $ip : if 0 remove the IP overwrite
*/
function updateMasterIP($ip) {
    //create template with changes
    $file = '/maasland_app/www/.extensions.php';
    //create extensions file
    if($ip) {
        mylog("set MasterIP overwrite to:".$ip);
        $extensions_tpl = new tpl();
        $extensions_tpl->newTemplate('/maasland_app/www/views/layout/extensions.tpl');
        $extensions_tpl->setVar('master_ip', $ip);

        // Adding opening php tag in the template gives an error
        // So we add it here in front of the result, 
        file_put_contents($file, "<?php \n".$extensions_tpl->grab());
        unset($extensions_tpl);
    } else {
        mylog("remove MasterIP overwrite");
        //remove file
        unlink($file);
    }
    //empty opcache, cli and webserver have seperate opcache, so do a restart to be sure
    //opcache_reset();
    //TODO restart needs to be delayed, to allow writing changes to db/filesystem
    //exec('/scripts/restart_services.sh');

    //header('refresh:5;url=http://'.$_SERVER['HTTP_HOST'].'/?/manage/network' ); 
    //header('Location: http://'.$_SERVER['HTTP_HOST'].'/?/manage/network');
    //redirect_to('http://'.$_SERVER['HTTP_HOST'].'/?/manage/network');
}

function updateNetworkMakeDHCP() {
    //copy orignal file with DHCP settings to interfaces
    $srcfile='/etc/network/interfaces.org';
    $dstfile='/etc/network/interfaces';
    copy($srcfile, $dstfile);
}

function updateNetwork($ip, $netmask, $gateway) {
    //make backup
    copy('/etc/network/interfaces', '/etc/network/interfaces~');

    //create template with changes
    $network_tpl = new tpl();
    $network_tpl->newTemplate('/maasland_app/www/views/layout/network_interfaces.tpl');

    $network_tpl->setVar('ip_address', $ip);
    $network_tpl->setVar('netmask', $netmask);
    $network_tpl->setVar('gateway', $gateway);
    //$network_tpl->setVar('broadcast', $this->broadcast($server_config['ip_address'], $server_config['netmask']));
    //$network_tpl->setVar('network', $this->network($server_config['ip_address'], $server_config['netmask']));

    file_put_contents('/etc/network/interfaces', $network_tpl->grab());
    unset($network_tpl);

    //restart network to load changes
    //exec('/etc/init.d/S40network restart');

    //header('Location: http://'.$_SERVER['HTTP_HOST'].'/?/manage/network');
}

function settings_replicate() {
    $result = replicate_to_slaves();
    saveReport("WebAdmin", "Configuration replicated to slave.");
    set('id', 7);
    set('title', "Replicate");
    set('content', $result);
    return html('page.html.php');
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
