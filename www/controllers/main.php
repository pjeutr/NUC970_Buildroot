<?php

require "lib/csv-9.5.0/autoload.php";
use League\Csv\Writer;
use League\Csv\Reader;

# GET /
function main_page() {
    return html('main.html.php');
}
function report_index() {
    set('reports', find_reports());
    return html('reports.html.php');
}
function report_csv() {
    //t
    $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
    //https://csv.thephpleague.com/9.0/interoperability/encoding/
    //let's set the output BOM
    $csv->setOutputBOM(Reader::BOM_UTF8);
    //let's convert the incoming data from iso-88959-15 to utf-8
    //$csv->addStreamFilter('convert.iconv.ISO-8859-15/UTF-8');
    $results = find_reports();

    $dbh = option('db_conn');
    $sth = $dbh->prepare(
        "SELECT id,user,door,created_at FROM reports LIMIT 1000"
    );
    //because we don't want to duplicate the data for each row
    // PDO::FETCH_NUM could also have been used
    $sth->setFetchMode(PDO::FETCH_ASSOC);
    $sth->execute();

    $filename = "reports_".date("Y-m-d_H:i:s");
    $columns = ["id","user","door","created_at"];
    $csv->insertAll($sth);
    $csv->output(
        //to get output in browser escape the next line/filename
        $filename.'.csv'
    );
    exit(); //safari was giving .html, this ends it
}
function timezone_index() {
    set('timezones', find_timezones());
    return html('timezones.html.php');
}
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

    set('message', $message);
    set('settings', find_settings());
    return html('settings.html.php');
}

//ajax
function last_reports() {
    return (json(find_reports()));
}
function last_scanned_key() {
    return get_last_scanned_key();
}

//TODO ajaxify
//user render ipv html 
//render('index.html.php', null, array('name' => 'John Doe' ));
//of json($my_data);
function gpio_key() {
	return (rand(0, 1)) ? "button on" : "button off";
}

function gpio_state() {
	$id = filter_var(params('id'), FILTER_VALIDATE_INT);
	$state = filter_var(params('state'), FILTER_VALIDATE_INT);
	setGPIO($id, $state);
    return html('dashboard.html.php');
}

function door_open() {
	$id = filter_var(params('id'), FILTER_VALIDATE_INT);
    saveReport("WebAdmin", "Opened door ".$id);
    openDoor($id);
    return html('dashboard.html.php');
}

