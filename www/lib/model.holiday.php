<?php

function find_holidays() {
    return find_objects_by_sql("SELECT * FROM `holidays`");
}

function find_holiday_by_id($id) {
    $sql =
        "SELECT * " .
        "FROM holidays " .
        "WHERE id=:id";
    return find_object_by_sql($sql, array(':id' => $id));
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_holiday_obj($holiday_obj) {
    return update_object($holiday_obj, 'holidays', holiday_columns());
}

function create_holiday_obj($holiday_obj) {
    return create_object($holiday_obj, 'holidays', holiday_columns());
}

function delete_holiday_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'holidays');
}

function delete_holiday_by_id($holiday_id) {
    delete_object_by_id($holiday_id, 'holidays');
}

function make_holiday_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function holiday_columns() {
    return array('name', 'start_date', 'end_date', 'created_at', 'updated_at');
}
