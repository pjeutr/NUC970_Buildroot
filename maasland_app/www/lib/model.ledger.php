<?php

function find_ledgers() {
    return find_objects_by_sql("SELECT * FROM ledger");
}

function find_ledger_by_id($id) {
    $sql =
        "SELECT * " .
        "FROM ledger " .
        "WHERE id=:id";
    return find_object_by_sql($sql, array(':id' => $id));
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

function update_ledger_obj($ledger_obj) {
    return update_object($ledger_obj, 'ledger', ledger_columns());
}

function create_ledger_obj($ledger_obj) {
    return create_object($ledger_obj, 'ledger', ledger_columns());
}

function delete_ledger_obj($man_obj) {
    delete_object_by_id($man_obj->id, 'ledger');
}

function delete_ledger_by_id($ledger_id) {
    delete_object_by_id($ledger_id, 'ledger');
}

function make_ledger_obj($params, $obj = null) {
    return make_model_object($params, $obj);
}

function ledger_columns() {
    return array('name', 'time_in', 'time_out');
}
