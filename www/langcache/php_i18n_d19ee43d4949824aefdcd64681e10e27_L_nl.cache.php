<?php class L {
const language = 'Taal';
const user = 'gebruiker';
const group = 'groep';
const door = 'deur';
const timezone = 'tijdzone';
const report = 'rapport';
const setting = 'instelling';
const users = 'gebruikers';
const groups = 'groepen';
const doors = 'deuren';
const timezones = 'tijdzones';
const reports = 'rapporten';
const settings = 'instellingen';
const button_new = 'Nieuwe';
const button_edit = 'Veranderen';
const button_change = 'Veranderen';
const button_delete = 'Verwijderen';
const dashboard_name = 'dashboard';
const dashboard_title = 'Deze controller heeft %s';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}