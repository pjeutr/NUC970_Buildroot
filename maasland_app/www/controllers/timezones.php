<?php

# GET /timezones
function timezones_index() {
    set('timezones', find_timezones());
    return html('timezones/index.html.php');
}

# GET /timezones/:id
function timezones_show() {
    $timezone = get_timezone_or_404();
    set('timezone', $timezone);
    return html('timezones/show.html.php');
}

# GET /timezones/:id/edit
function timezones_edit() {
    $timezone = get_timezone_or_404();
    set('timezone', $timezone);
    //set('authors', find_authors());
    return html('timezones/edit.html.php');
}

# PUT /timezones/:id
function timezones_update() {
    $timezone_data = timezone_data_from_form();
    $timezone = get_timezone_or_404();
    $timezone = make_timezone_obj($timezone_data, $timezone);

    update_timezone_obj($timezone);
    redirect('timezones');
}

# GET /timezones/new
function timezones_new() {
    $timezone_data = make_empty_obj(timezone_columns());
    set('timezone', make_timezone_obj($timezone_data));
    //set('authors', find_authors());
    return html('timezones/new.html.php');
}

# POST /timezones
function timezones_create() {
    $timezone_data = timezone_data_from_form();
    $timezone = make_timezone_obj($timezone_data);

    create_timezone_obj($timezone);
    redirect('timezones');
}

# DELETE /timezones/:id
function timezones_destroy() {
    delete_timezone_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    redirect('timezones');
}

function get_timezone_or_404() {
    $timezone = find_timezone_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    if (is_null($timezone)) {
        halt(NOT_FOUND, "This timezone doesn't exist.");
    }
    return $timezone;
}

function timezone_data_from_form() {
    return isset($_POST['timezone']) && is_array($_POST['timezone']) ? $_POST['timezone'] : array();
}
