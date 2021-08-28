<?php

function find_settings() {
    return find_objects_by_sql("SELECT * FROM settings WHERE status=1");
}

function find_setting_by_id($id) {
    $sql =
        "SELECT value " .
        "FROM settings " .
        "WHERE id=".$id;
        //"WHERE id=:id"; werkt niet?
    return find_string_by_sql($sql, array(':id' => $id));
}

function find_setting_by_name($name) {
    $sql =
        "SELECT value " .
        "FROM settings " .
        "WHERE name='".$name."'";
    return find_string_by_sql($sql, array(':name' => $name));
}

function check_password($v) {
    $r = find_string_by_sql("SELECT value FROM `settings` WHERE name='password'");
    if($v == $r) {
        return true;
    }
    return false;
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_setting_obj($setting_obj) {
    return update_object($setting_obj, 'settings', setting_columns());
}

function create_setting_obj($setting_obj) {
    return create_object($setting_obj, 'settings', setting_columns());
}

function delete_setting_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'settings');
}

function delete_setting_by_id($setting_id) {
    delete_object_by_id($setting_id, 'settings');
}

function make_setting_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function setting_columns() {
    return array('name', 'created_at', 'updated_at');
}





