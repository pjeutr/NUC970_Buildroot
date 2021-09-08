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
        "SELECT id,user,door,created_at FROM reports LIMIT 5000"
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
	$id = filter_var(params('id'), FILTER_VALIDATE_INT);
    $controllerId = filter_var(params('controller'), FILTER_VALIDATE_INT);
    saveReport("WebAdmin", "Opened door ".$id);
    $r = openDoor($id, $controllerId);
    return (json(array($r)));
}

