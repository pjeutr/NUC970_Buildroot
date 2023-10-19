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

function count_presents() { 
    $sql = $sql =
        "SELECT count(case when present = 0 then 1 else null end) as bye," .
        "count(case when present = 1 then 1 else null end) as hi ".
        "FROM ledger";
    return find_object_by_sql($sql);
}

function update_ledger($user_obj, $readerId) {
    mylog("update_ledger=".$user_obj->name."=".$readerId);
    $user_id = $user_obj->id;
    $name = $user_obj->name;
    $key = $user_obj->keycode;
    //$now = DateTime('now');

    if($readerId == 1) {
        $sql = "INSERT OR REPLACE INTO ledger (user_id,name,present,keycode,time_in,time_out) VALUES ($user_id, '$name',1,'$key', DateTime('now'), null)";

            //"UPDATE ledger SET present = 1, time_out= null, time_in = DateTime('now') WHERE user_id LIKE $user_id;";

        //$sql = "UPDATE ledger SET last_seen = DateTime('now'), visit_count=visit_count+1  WHERE id = ".$user_obj->id;
    } else {
        $sql = "UPDATE ledger SET name='".$name."', present=0, keycode='".$key."', time_out=DateTime('now') WHERE user_id = '".$user_id."'";
    }
    
    mylog($sql);
    return update_object_with_sql($sql, 'users');
    //return "";
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
