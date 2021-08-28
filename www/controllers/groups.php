<?php

# GET /groups
function groups_index() {
    set('groups', find_groups());
    set('doors', find_doors());
    set('timezones', find_timezones());
    set('rules', find_rules());
    return html('groups/index.html.php');
}

# GET /groups/:id
function groups_show() {
    $group = get_group_or_404();
    set('group', $group);
    return html('groups/show.html.php');
}

# GET /groups/:id/edit
function groups_edit() {
    $group = get_group_or_404();
    set('group', $group);
    //set('authors', find_authors());
    return html('groups/edit.html.php');
}

# PUT /groups/:id
function groups_update() {
    $group_data = group_data_from_form();
    $group = get_group_or_404();
    $group = make_group_obj($group_data, $group);

    update_group_obj($group);
    redirect('groups');
}

# GET /groups/new
function groups_new() {
    $group_data = make_empty_obj(group_columns());
    set('group', make_group_obj($group_data));
    //set('authors', find_authors());
    return html('groups/new.html.php');
}

# POST /groups
function groups_create() {
    $group_data = group_data_from_form();
    $group = make_group_obj($group_data);

    create_group_obj($group);
    redirect('groups');
}

# DELETE /groups/:id
function groups_destroy() {
    delete_group_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    redirect('groups');
}

# PUT /grules/:id
function grules_update() {
    $rule_data = rule_data_from_form2();
    $rule = get_grule_or_404();
    $rule = make_rule_obj($rule_data, $rule);

    update_rule_obj($rule);
    redirect('groups');
}

# POST /grules
function grules_create() {
    $rule_data = rule_data_from_form2();
    $rule = make_rule_obj($rule_data);
    try {
        create_rule_obj($rule);
    } catch(PDOException $e) {
        //TODO SQLSTATE[23000]: Integrity constraint violation: 19
        mylog($e);
        flash('message', 'That door was already assigned to this group!');
    }
    redirect('groups');
}

# DELETE /grules/:id
function grules_destroy() {
    delete_rule_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    redirect('groups');
}
function get_grule_or_404() {
    $rule = find_rule_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    if (is_null($rule)) {
        halt(NOT_FOUND, "This rule doesn't exist.");
    }
    return $rule;
}

function get_group_or_404() {
    $group = find_group_by_id(filter_var(params('id'), FILTER_VALIDATE_INT));
    if (is_null($group)) {
        halt(NOT_FOUND, "This group doesn't exist.");
    }
    return $group;
}
function rule_data_from_form2() {
    return isset($_POST['rule']) && is_array($_POST['rule']) ? $_POST['rule'] : array();
}

function group_data_from_form() {
    return isset($_POST['group']) && is_array($_POST['group']) ? $_POST['group'] : array();
}
