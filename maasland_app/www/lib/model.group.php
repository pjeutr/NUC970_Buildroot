<?php

function find_groups() {
    return find_objects_by_sql("SELECT * FROM `groups`");
}

function find_group_by_id($id) {
    $sql =
        "SELECT * " .
        "FROM groups " .
        "WHERE id=:id";
    return find_object_by_sql($sql, array(':id' => $id));
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_group_obj($group_obj) {
    return update_object($group_obj, 'groups', group_columns());
}

function create_group_obj($group_obj) {
    return create_object($group_obj, 'groups', group_columns());
}

function delete_group_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'groups');
}

function delete_group_by_id($group_id) {
    delete_object_by_id($group_id, 'groups');
}

function make_group_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function group_columns() {
    return array('name', 'created_at', 'updated_at');
}
