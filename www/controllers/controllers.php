<?php

function available_controllers() {
    return json(mdnsBrowse("_maasland._udp"));
}

# GET /controllers
function controllers_index() {
    set('controllers', find_controllers());
    return html('controllers/index.html.php');
}

# GET /controllers/:id
function controllers_show() {
    $controller = get_controller_or_404();
    set('controller', $controller);
    return html('controllers/show.html.php');
}

# GET /controllers/:id/edit
function controllers_edit() {
    $controller = get_controller_or_404();
    set('controller', $controller);

    return html('controllers/edit.html.php');
}

# PUT /controllers/:id
function controllers_update() {
    $controller_data = controller_data_from_form();
    $controller = get_controller_or_404();
    $controller = make_controller_obj($controller_data, $controller);

    update_controller_obj($controller);
    redirect('controllers');
}

# GET /controllers/new
function controllers_new() {
    $controller_data = make_empty_obj(controller_columns());
    set('controller', make_controller_obj($controller_data));
    return html('controllers/new.html.php');
}

# POST /controllers
function controllers_create() {
    $controller_data = controller_data_from_form();
    $controller = make_controller_obj($controller_data);
    $controllerId = create_controller_obj($controller);
    mylog("controllers_create id=".$controllerId);
    //When creating a controller, we also create the 2 doors
    create_door_obj(make_door_obj(array('name' => 'Door 1','controller_id' => $controllerId,'timezone_id' => null )));
    create_door_obj(make_door_obj(array('name' => 'Door 2','controller_id' => $controllerId,'timezone_id' => null) ));

    redirect('controllers');
}

# DELETE /controllers/:id
function controllers_destroy() {
    delete_controller_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));

    //When deleting a controller, we also neet to delete the asociated doors
    delete_door_by_controller_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    redirect('controllers');
}

function get_controller_or_404() {
    $controller = find_controller_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    if (is_null($controller)) {
        halt(NOT_FOUND, "This controller doesn't exist.");
    }
    return $controller;
}

function controller_data_from_form() {
    return isset($_POST['controller']) && is_array($_POST['controller']) ? $_POST['controller'] : array();
}
