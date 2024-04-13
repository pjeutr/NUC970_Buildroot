<?php

# GET /holidays
function holidays_index() {
    set('holidays', find_holidays());
    return html('holidays/index.html.php');
}

# GET /holidays/:id
function holidays_show() {
    $holiday = get_holiday_or_404();
    set('holiday', $holiday);
    return html('holidays/show.html.php');
}

# GET /holidays/:id/edit
function holidays_edit() {
    $holiday = get_holiday_or_404();
    set('holiday', $holiday);
    //set('authors', find_authors());
    return html('holidays/edit.html.php');
}

# PUT /holidays/:id
function holidays_update() {
    $holiday_data = holiday_data_from_form();
    $holiday = get_holiday_or_404();
    $holiday = make_holiday_obj($holiday_data, $holiday);

    update_holiday_obj($holiday);
    redirect('holidays');
}

# GET /holidays/new
function holidays_new() {
    $holiday_data = make_empty_obj(holiday_columns());
    set('holiday', make_holiday_obj($holiday_data));
    //set('authors', find_authors());
    return html('holidays/new.html.php');
}

# POST /holidays
function holidays_create() {
    $holiday_data = holiday_data_from_form();
    $holiday = make_holiday_obj($holiday_data);

    create_holiday_obj($holiday);
    redirect('holidays');
}

# DELETE /holidays/:id
function holidays_destroy() {
    delete_holiday_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    redirect('holidays');
}

function get_holiday_or_404() {
    $holiday = find_holiday_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    if (is_null($holiday)) {
        halt(NOT_FOUND, "This holiday doesn't exist.");
    }
    return $holiday;
}

function holiday_data_from_form() {
    return isset($_POST['holiday']) && is_array($_POST['holiday']) ? $_POST['holiday'] : array();
}
