<?php
/*
    input 
    1,2 are readers
    3,4 are buttons
    5,6 are sensors
    output
    1,2 are doors
    3,4 are alarms
*/

# PUT /controller/:id
/* Change input values, happens on doors form */
function input_update() {
    $id = filter_var(params('id'), FILTER_VALIDATE_INT);
    $switch = filter_var_array($_POST['switch'], FILTER_SANITIZE_STRING);
    $sensor = filter_var_array($_POST['sensor'], FILTER_SANITIZE_STRING);
    mylog("input_update controllerId=".$id);

    $sql = "UPDATE controllers SET reader_1 = ?, reader_2 = ?, button_1 = ?, button_2 = ?, sensor_1 = ?, sensor_2 = ? WHERE id = ?";

    $swalMessage = swal_message_error("Something went wrong!");
    if(update_with_sql($sql, [$switch[1],$switch[2],$switch[3],$switch[4],$sensor[1],$sensor[2],$id])) {
        $swalMessage = swal_message_success(L("message_changes_saved"));
    }

    set('swalMessage', $swalMessage);

    //redirect('doors');
    set('controllers', find_all_controllers());
    set('doors', find_doors());
    return html('doors/index.html.php');
}