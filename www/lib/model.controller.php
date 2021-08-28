<?php

function find_controllers() {
    return find_objects_by_sql("SELECT * FROM `controllers`");
}

function find_controller_by_id($id) {
    $sql =
        "SELECT * " .
        "FROM controllers " .
        "WHERE id=:id";
    return find_object_by_sql($sql, array(':id' => $id));
}

function find_controller_by_ip($ip) {
    $sql =
        "SELECT * " .
        "FROM controllers " .
        "WHERE ip=:ip";
    return find_object_by_sql($sql, array(':ip' => $ip));
}

function find_door_for_input_device($id, $cid) {
    $sql = "SELECT d.id, d.name, d.timezone_id FROM controllers c, doors d WHERE c.id = ".$cid." AND d.id = c.".$id;
    return find_object_by_sql($sql);
}

function find_door_for_reader_id($id, $cid) {
    $sql = "SELECT d.id, d.name, d.timezone_id FROM controllers c, doors d WHERE c.id = ".$cid." AND d.id = c.reader_".$id;
    return find_object_by_sql($sql);
}

function find_alarm_for_sensor_id($id, $cid) {
    $sql = "SELECT sensor_".$id." FROM controllers c, doors d WHERE c.id = ".$cid;
    return find_object_by_sql($sql);
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_controller_obj($controller_obj) {
    return update_object($controller_obj, 'controllers', controller_columns());
}

function create_controller_obj($controller_obj) {
    return create_object($controller_obj, 'controllers', controller_columns());
}

function delete_controller_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'controllers');
}

function delete_controller_by_id($controller_id) {
    delete_object_by_id($controller_id, 'controllers');
}

function make_controller_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function controller_columns() {
    return array('name', 'created_at', 'updated_at');
}
