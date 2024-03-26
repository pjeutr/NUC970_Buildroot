<?php

require "lib/csv-9.5.0/autoload.php";
use League\Csv\Writer;
use League\Csv\Reader;

# GET /
function main_page() {
    return html('main.html.php');
}

# GET /ledger
function ledger_index() {
    set('ledger', find_ledgers());
    set('presents', count_presents());
    return html('ledger.html.php'); 
}
# DELETE /ledger/:id
function ledger_destroy() {
    delete_ledger_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    redirect('ledger');
}
function ledger_csv() {
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
        "SELECT name,present,keycode,time_in,time_out FROM ledger LIMIT 5000"
    );
    //because we don't want to duplicate the data for each row
    // PDO::FETCH_NUM could also have been used
    $sth->setFetchMode(PDO::FETCH_ASSOC);
    $sth->execute();

    $filename = "present_".date("Y-m-d_H:i:s");
    $columns = ["user","present","keycode","time in", "time out"];
    $csv->insertAll($sth);
    $csv->output(
        //to get output in browser escape the next line/filename
        $filename.'.csv'
    );
    exit(); //safari was giving .html, this ends it
}
function report_index() {
    set('reports', find_reports());
    return html('reports.html.php');
}
function report_csv() {

    //adjust date to the local timeZone
    $convertDate = function ($row) {
        if(isset($row['created_at'])){
            $row['created_at'] = print_date($row['created_at']);
        }
        return $row;
    };

    $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
    //https://csv.thephpleague.com/9.0/interoperability/encoding/
    //Set byte-order mark, for compatibility with Microsoft eco system
    $csv->setOutputBOM(Reader::BOM_UTF8);

    //get data for DB
    $dbh = option('db_conn');
    $sth = $dbh->prepare(
        "SELECT keycode,user,door,created_at FROM reports ORDER BY created_at DESC LIMIT 4999"
    );

    // because we don't want to duplicate the data for each row
    // PDO::FETCH_NUM could also have been used
    $sth->setFetchMode(PDO::FETCH_ASSOC);
    $sth->execute();

    $filename = "reports_".date("Y-m-d_H:i:s");
    $columns = ["keycode","user","door","time"];
    $csv->insertOne($columns);
    $csv->addFormatter($convertDate);
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
    $result = changeOutputState($outputEnum, $controller, $door, $state);
    return (json(array($result)));
}
function checkCleanupReports() {
    //delete rows older than x days in reports
    $days = 7;
    $action = cleanupReports($days);
    mylog($action);
    if($action > 0) {
        saveReport("WebAdmin", "Older than $days days. $action rows deleted in reports.");
    }
    $result = getOutputStatus($outputId);
    return json($result);
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
function callFunction() {
    $name = filter_var(params('name'), FILTER_VALIDATE_INT);
    $value = filter_var(params('value'), FILTER_VALIDATE_INT);
    $result = beepMessageBuzzer($value);
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



