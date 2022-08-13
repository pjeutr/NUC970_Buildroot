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
        "SELECT user,door,created_at FROM reports LIMIT 5000"
    );
    //because we don't want to duplicate the data for each row
    // PDO::FETCH_NUM could also have been used
    $sth->setFetchMode(PDO::FETCH_ASSOC);
    $sth->execute();

    $filename = "reports_".date("Y-m-d_H:i:s");
    $columns = ["user","door","created_at"];
    $csv->insertAll($sth);
    $csv->output(
        //to get output in browser escape the next line/filename
        $filename.'.csv'
    );
    exit(); //safari was giving .html, this ends it
}

//ajax
function last_reports() {
    return (json(find_reports()));
}
function last_scanned_key() {
    return get_last_scanned_key();
}
function gpio_key() {
	return (rand(0, 1)) ? "button on" : "button off";
}
function gpio_state() {
	$id = filter_var(params('id'), FILTER_VALIDATE_INT);
	$state = filter_var(params('state'), FILTER_VALIDATE_INT);
	$r = setGPIO($id, $state);
    return (json(array($r)));
}
function door_open() {
	$doorId = filter_var(params('door'), FILTER_VALIDATE_INT);
    $controllerId = filter_var(params('controller'), FILTER_VALIDATE_INT);
    $door = find_door_by_id($doorId);
    $controller = find_controller_by_id($controllerId);
    $result = openDoor($door, $controller);
    saveReport("WebAdmin", $door->name);//."@".$controller->name);
    return (json(array($result)));
}
function switchOutput() {
    $outputEnum = filter_var(params('output'), FILTER_VALIDATE_INT);
    $controllerId = filter_var(params('controller'), FILTER_VALIDATE_INT);
    $state = filter_var(params('state'), FILTER_VALIDATE_INT);
    $door = find_door_for_enum($outputEnum, $controllerId);
    $controller = find_controller_by_id($controllerId);
    saveReport("WebAdmin", "Switch ".$door->name." ".($state?"open":"closed")." on ".$controller->name);
    $result = changeOutputState($outputEnum, $controller, $state);
    return (json(array($result)));
}
function outputStatus() {
    $outputId = filter_var(params('door'), FILTER_VALIDATE_INT);
    $result = getOutputStatus($outputId);
    return json($result);
}
function output() {
    $door = filter_var(params('door'), FILTER_VALIDATE_INT);
    $state = filter_var(params('state'), FILTER_VALIDATE_INT);
    $result = operateOutput($door, $state);
    return (json(array($result)));
}
function activate() {
    $door = filter_var(params('door'), FILTER_VALIDATE_INT);
    $duration = filter_var(params('duration'), FILTER_VALIDATE_INT);
    $gpiosString = params('gpios');
    //put gpios in an array
    $gpios = explode("-",$gpiosString);
    $result = activateOutput($door, $duration, $gpios);
    return (json(array($result)));
}
function input() {
    $from = $_SERVER['REMOTE_ADDR'];
    $input = filter_var(params('input'), FILTER_VALIDATE_INT);
    $keycode = filter_var(params('keycode'), FILTER_VALIDATE_INT);
    mylog("input ".$from);
    $result = handleInput($from, $input, $keycode);
    return (json(array($result)));
}



