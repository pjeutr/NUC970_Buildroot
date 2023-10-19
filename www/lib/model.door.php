<?php

// function find_doors() {
//     $sql =
//         "SELECT " .
//         "b.id as id, b.name as name, b.year as year, b.user_id as user_id, " .
//         "a.name as user_name, a.birthday as user_birthdady, a.bio as user_bio " .
//         "FROM doors b " .
//         "LEFT JOIN users a ON a.id=b.user_id";
//     return find_objects_by_sql($sql);
// }

// function find_door_by_id($id) {
//     $sql =
//         "SELECT " .
//         "b.id as id, b.name as name, b.year as year, b.user_id as user_id, " .
//         "a.name as user_name, a.birthday as user_birthdady, a.bio as user_bio " .
//         "FROM doors b " .
//         "LEFT JOIN users a ON a.id=b.user_id " .
//         "WHERE b.id=:id";
//     return find_object_by_sql($sql, array(':id' => $id));
// }

function find_doors() {
    return find_objects_by_sql("SELECT * FROM `doors`");
}

function find_door_by_id($id) {
    $sql =
        "SELECT * " .
        "FROM doors " .
        "WHERE id=:id";
    return find_object_by_sql($sql, array(':id' => $id));
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_door_obj($door_obj) {
    return update_object($door_obj, 'doors', door_columns());
}

function create_door_obj($door_obj) {
    return create_object($door_obj, 'doors', door_columns());
}

function delete_door_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'doors');
}

function delete_door_by_id($door_id) {
    delete_object_by_id($door_id, 'doors');
}

function make_door_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function door_columns() {
    return array('name','timezone_id');
}





