<?php

function find_timezones() {
    return find_objects_by_sql("SELECT * FROM `timezones`");
}

function find_timezone_by_id($id) {
    $sql =
        "SELECT * " .
        "FROM timezones " .
        "WHERE id=:id";
    return find_object_by_sql($sql, array(':id' => $id));
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_timezone_obj($timezone_obj) {
    return update_object($timezone_obj, 'timezones', timezone_columns());
}

function create_timezone_obj($timezone_obj) {
    return create_object($timezone_obj, 'timezones', timezone_columns());
}

function delete_timezone_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'timezones');
}

function delete_timezone_by_id($timezone_id) {
    delete_object_by_id($timezone_id, 'timezones');
}

function make_timezone_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function timezone_columns() {
    return array('name', 'start', 'end', 'weekdays', 'created_at', 'updated_at');
}
