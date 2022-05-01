<?php

function find_reports() {
    //OOM @ >1500 in webpage, using 500 to be save
    return find_objects_by_sql("SELECT * FROM `reports` ORDER BY id DESC LIMIT 500");
}

function find_report_by_id($id) {
    $sql =
        "SELECT * " .
        "FROM reports " .
        "WHERE id=:id";
    return find_object_by_sql($sql, array(':id' => $id));
}

function get_last_scanned_key() {
    //if a unkown key is scanned. It will be written to user in reports
    return find_string_by_sql("SELECT keycode FROM reports ORDER BY id DESC LIMIT 1"); 
}

function cleanupReports($days) {
    mylog("cleanupReports");
    //cleanup reports, older than x days
    $count = delete_objects_where("created_at < date('now', '-7 day')", 'reports'); 
    mylog($count);
    return $count;
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_report_obj($report_obj) {
    return update_object($report_obj, 'reports', report_columns());
}

function create_report_obj($report_obj) {
    return create_object($report_obj, 'reports', report_columns());
}

function delete_report_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'reports');
}

function delete_report_by_id($report_id) {
    delete_object_by_id($report_id, 'reports');
}

function make_report_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function report_columns() {
    return array('id', 'door', 'user', 'created_at', 'updated_at');
}
