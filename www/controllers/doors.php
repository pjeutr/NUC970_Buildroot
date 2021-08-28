<?php

# GET /doors
function doors_index() {
    set('controllers', find_controllers());
    //TODO put doors in controllers
    set('doors', find_doors());
    return html('doors/index.html.php');
}

# GET /doors/:id
function doors_show() {
    $door = get_door_or_404();
    set('door', $door);
    return html('doors/show.html.php');
}

# GET /doors/:id/edit
function doors_edit() {
    $door = get_door_or_404();
    set('door', $door);
    set('timezones', find_timezones());
    return html('doors/edit.html.php');
}

# PUT /doors/:id
function doors_update() {
    $door_data = door_data_from_form();
    $door = get_door_or_404();
    $door = make_door_obj($door_data, $door);

    update_door_obj($door);
    redirect('doors');
}

# GET /doors/new
function doors_new() {
    $door_data = make_empty_obj(door_columns());
    set('door', make_door_obj($door_data));
    set('timezones', find_timezones());
    return html('doors/new.html.php');
}

# POST /doors
function doors_create() {
    $door_data = door_data_from_form();
    $door = make_door_obj($door_data);

    create_door_obj($door);
    redirect('doors');
}

# DELETE /doors/:id
function doors_destroy() {
    delete_door_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    redirect('doors');
}

function get_door_or_404() {
    $door = find_door_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    if (is_null($door)) {
        halt(NOT_FOUND, "This door doesn't exist.");
    }
    return $door;
}

function door_data_from_form() {
    return isset($_POST['door']) && is_array($_POST['door']) ? $_POST['door'] : array();
}
