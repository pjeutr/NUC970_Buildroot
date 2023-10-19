<?php

# GET /user
function users_index() {
    set('users', find_users());
    return html('users/index.html.php');
}

# GET /users/:id
function users_show() {
    $user = get_user_or_404();
    set('user', $user);
    return html('users/show.html.php');
}

# GET /users/:id/edit
function users_edit() {
    $user = get_user_or_404();
    set('user', $user);
    set('groups', find_groups());
    return html('users/edit.html.php');
}

# PUT /users/:id
function users_update() {
    $user_data = user_data_from_form();
    //error_log(json_encode($user_data));
    $user = get_user_or_404();
    $user = make_user_obj($user_data, $user);

    try {
        update_user_obj($user);
    } catch(PDOException $e) {
        //PDOException: SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: users.keycode
        mylog($e);
        flash('message', 'That keycode was already assigned to a user!');
        redirect('users/'.$user->id.'/edit');
    }
    redirect('users');
}

# GET /users/new
function users_new() {
    $user_data = make_empty_obj(user_columns());
    error_log(json_encode($user_data));
    set('user', make_user_obj($user_data));
    set('groups', find_groups());
    return html('users/new.html.php');
}

# POST /users
function users_create() {
    $user_data = user_data_from_form();
    $user = make_user_obj($user_data);
    //visit_count can not be empty, if we want to use autoincrement
    $user->visit_count=0;

    try {
        create_user_obj($user);
    } catch(PDOException $e) {
        //PDOException: SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: users.keycode
        mylog($e);
        flash('message', 'That keycode was already assigned to a user!');
        redirect('users/new');
    }
    redirect('users');
}

# DELETE /users/:id
function users_destroy() {
    delete_user_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    redirect('users');
}

function get_user_or_404() {
    $user = find_user_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    if (is_null($user)) {
        halt(NOT_FOUND, "This user doesn't exist.");
    }
    return $user;
}

function user_data_from_form() {
    return isset($_POST['user']) && is_array($_POST['user']) ? $_POST['user'] : array();
}
