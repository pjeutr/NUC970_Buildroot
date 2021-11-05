<?php class L {
const language = 'Language';
const user = 'user';
const group = 'group';
const door = 'door';
const timezone = 'timezone';
const report = 'report';
const setting = 'setting';
const users = 'users';
const groups = 'groups';
const doors = 'doors';
const timezones = 'timezones';
const reports = 'reports';
const settings = 'settings';
const button_new = 'New';
const button_edit = 'Edit';
const button_change = 'Change';
const button_delete = 'Delete';
const dashboard_name = 'dashboard';
const dashboard_title = 'This controller has %s';
const message_slave = 'This is a slave controller';
const message_factoryreset = 'The factory settings switch is on.<br>Factory settings were put back and the old configuration is deleted';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}