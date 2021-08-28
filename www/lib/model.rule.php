<?php

function find_rules() { 
    $sql = "SELECT " .
        "b.id as id, b.name as name, b.group_id as group_id, b.door_id as door_id, b.timezone_id as timezone_id, " .
        "g.name as group_name, d.name as door_name, t.name as timezone_name " .
        "FROM rules b " .
        "LEFT JOIN timezones t ON t.id=b.timezone_id ".
        "LEFT JOIN doors d ON d.id=b.door_id ".
        "LEFT JOIN groups g ON g.id=b.group_id";
    return find_objects_by_sql($sql);
}

function find_timezone_by_group_id($group_id, $door_id) {
    $sql = "SELECT " .
        "t.end end, t.start start, t.weekdays weekdays , t.name name " .
        "FROM rules r, timezones t " .
        "WHERE r.group_id=:group_id ".
        "AND r.door_id=:door_id ".
        "AND r.timezone_id=t.id ";
    return find_object_by_sql($sql, array(':group_id' => $group_id, ':door_id' => $door_id));
}

function find_rule_by_id($id) {
    $sql = "SELECT * " .
        "FROM rules " .
        "WHERE id=:id";
    return find_object_by_sql($sql, array(':id' => $id));
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_rule_obj($rule_obj) {
    return update_object($rule_obj, 'rules', rule_columns());
}

function create_rule_obj($rule_obj) {
    return create_object($rule_obj, 'rules', rule_columns());
}

function delete_rule_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'rules');
}

function delete_rule_by_id($rule_id) {
    delete_object_by_id($rule_id, 'rules');
}

function make_rule_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function rule_columns() {
    return array('group_id','door_id','timezone_id');
}





