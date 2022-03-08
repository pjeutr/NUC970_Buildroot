<?php

function find_controllers() {
    return find_objects_by_sql("SELECT * FROM controllers");
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
    $sql = "SELECT d.id, d.name, d.enum, d.timezone_id FROM controllers c, doors d WHERE d.controller_id = ".$cid." AND c.id = d.controller_id AND d.enum = c.".$id;
    return find_object_by_sql($sql);
}

function find_door_for_reader_id($id, $cid) {
    $sql = "SELECT d.id, d.name, d.timezone_id FROM controllers c, doors d WHERE c.id = ".$cid." AND d.id = c.reader_".$id;
    return find_object_by_sql($sql);
}

function find_alarm_for_sensor_id($id, $cid) {
    $sql = "SELECT ".$id." FROM controllers c WHERE c.id = ".$cid;
    return find_string_by_sql($sql);
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_controller_obj($controller_obj) {
    return update_object($controller_obj, 'controllers', controller_columns());
}

function create_controller_obj($controller_obj) {
    $columns = controller_columns();
    $controller_obj->reader_1 = 1;
    $controller_obj->reader_2 = 1;
    $controller_obj->button_1 = 1;
    $controller_obj->button_2 = 1;
    $controller_obj->sensor_1 = 1;
    $controller_obj->sensor_2 = 1;
    mylog(json_encode($controller_obj));
    return create_object($controller_obj, 'controllers', $columns);
}

function delete_controller_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'controllers');
}

function delete_controller_by_id($controller_id) {
    delete_object_by_id($controller_id, 'controllers');
}

function delete_door_by_controller_id($controller_id) {
    $db = option('db_conn');
    $stmt = $db->prepare("DELETE FROM doors WHERE controller_id = ?");
    $stmt->execute(array($controller_id));
}

function make_controller_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function controller_columns() {
    // Creating a new controller gives these erros, not critical.
    // And currently practical to start/test the error log when logging is disabled
    // PHP Notice:  Undefined property: stdClass::$created_at in /maasland_app/www/lib/db.php on line 79
    // PHP Notice:  Undefined property: stdClass::$updated_at in /maasland_app/www/lib/db.php on line 79

    return array('name', 'ip', 'remarks', 
        'reader_1', 'reader_2', 'button_1', 'button_2', 'sensor_1', 'sensor_2', 
        'created_at', 'updated_at');
}
