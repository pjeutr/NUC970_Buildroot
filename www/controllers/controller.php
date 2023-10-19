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

# GET /controller/:id/input/:input/
/* 
    Incoming input from other conrollers, 
    Determine what door needs to open or alarm activate and call action on controller 
    Door 1 = http://controller_ip/?/door/1
    Alarm 1 = http://controller_ip/?/door/3
*/
function controller_input() {
    //http://maasland/?/controller/2/input/6
    $id = filter_var(params('id'), FILTER_VALIDATE_INT);
    $input = filter_var(params('input'), FILTER_VALIDATE_INT);
    error_log("controller=".$id." input=".$input);
    error_log(print_r($_POST, true));
    set('message', 'incomming!');

    //find what door to open, for this input

    redirect('door/1');
}

# PUT /controller/:id
/* Change input values, happens on doors form */
function input_update() {
    $id = filter_var(params('id'), FILTER_VALIDATE_INT);
    $switch = filter_var_array($_POST['switch'], FILTER_SANITIZE_STRING);
    $sensor = filter_var_array($_POST['sensor'], FILTER_SANITIZE_STRING);
    mylog("input_update controllerId=".$id);

    $sql = "UPDATE controllers SET reader_1 = ?, reader_2 = ?, button_1 = ?, button_2 = ?, sensor_1 = ?, sensor_2 = ? WHERE id = ?";

    $message = "{type: 'error' ,title: 'Oops', text: 'Something went wrong!'}";
    if(update_with_sql($sql, [$switch[1],$switch[2],$switch[3],$switch[4],$sensor[1],$sensor[2],$id])) {
        $message = "{type: 'success' ,title: 'Great', text: 'The changes were save!'}";
    }

    set('message', $message);

    //redirect('doors');
    set('controllers', find_controllers());
    set('doors', find_doors());
    return html('doors/index.html.php');
}