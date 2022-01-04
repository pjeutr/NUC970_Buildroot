<?php class L {
const language = 'Language';
const user = 'user';
const group = 'group';
const door = 'door';
const timezone = 'timezone';
const report = 'report';
const setting_door_open = 'Door open duration (in seconds)';
const setting_sound_buzzer = 'Sound buzzer when opening door';
const setting_hostname = 'Hostname';
const setting_password = 'Password';
const setting_apb = 'Anti-passback(in seconds)';
const setting_alarm = 'Alarm (time in seconds)';
const setting_upload = 'Upload configuration';
const setting_download = 'Download configuration';
const controller = 'controller';
const edit = 'Edit';
const add = 'Add';
const new = 'New';
const users = 'Users';
const groups = 'Groups';
const doors = 'Doors';
const timezones = 'Timezones';
const reports = 'Reports';
const settings = 'Settings';
const controllers = 'Controllers';
const id = 'id';
const name = 'name';
const key = 'keycode';
const time = 'Time';
const key_sub = 'Enter a code';
const key_button = 'Use scanned key';
const choose_file = 'Choose file';
const key_remark = 'Type the pincode or the W26 code from tag';
const generic_sub = 'Enter a ';
const visits = 'Visits';
const lastseen = 'Last seen';
const action = 'action';
const startdate = 'start date';
const startdate_remark = 'Before this date the code/tag is invalid (empty is for ever)';
const enddate = 'end date';
const enddate_remark = 'After this date the code/tag is invalid (empty is for ever)';
const maxvisits = 'maximum visits';
const maxvisits_remark = 'After the maximum number of visits the key/code is invalid (empty is unlimited)';
const remarks = 'remarks';
const remarks_sub = 'Space for remarks';
const search_controller_button = 'Search for controllers';
const search_controller_remark = 'Search and select a controller to fill the fields below';
const networkaddress = 'Network address';
const choose = 'Choose...';
const timezone_warning = 'Attention! The door will automatically open at chosen timezone';
const timezone_remark = 'The door will automatically open at chosen timezone
Cancel';
const start = 'Start';
const end = 'End';
const weekdays = 'Weekdays';
const weekdays2 = 'Days of the week';
const value = 'Value';
const button_new = 'New';
const button_edit = 'Edit';
const button_change = 'Change';
const button_delete = 'Delete';
const button_confirm = 'Yes, Delete it!';
const button_cancel = 'Cancel';
const button_save = 'Save';
const button_newrule = 'New rule';
const button_downloadcsv = 'Download csv';
const button_logout = 'Log out';
const delete_confirm = 'Are you sure?';
const delete_subtext = 'This item will be deleted!';
const message_slave = 'This is a slave controller';
const message_factoryreset = 'The reset factory settings switch is on.<br>Factory settings were put back and the old configuration is deleted';
const message_db_error = 'Something went wrong with the configuration.<br> Try to reset factory settings, by using the proper switch.';
const message_unkown_error = 'Something went wrong';
const dashboard_name = 'Dashboard';
const dashboard_buttons = 'Open Door';
const dashboard_title = 'This controller has %s';
const dashboard_text1 = '<div class=\'typography-line\'><p>
<span>Hardware</span></p><ul>
<li>2 relays outputs - to connect to doorlocks</li>
<li>2 Wiegand inputs - to connect to keypad or cardreader</li>
<li>2 alarm outputs - to connect to alarms</li>
<li>2 monitor inputs - to connect to door monitors</li>
<li>UTP connector - to connect to a LAN</li>
<li>Voltage in - to connect 8-24VDC</li>
</ul><p></p></div>';
const dashboard_text2 = '<div class=\'typography-line\'>
<span>Configuration</span><ol>
<li>Add doors from this Master controller, or from other Slave controllers</li>
<li>Add timezones (24h and working hours are predefined)</li>
<li>Create groups with timezones</li>
<li>Create users and assign them to a group</li>
<li>Add a code or tag to the user</li>
</ol></div>';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}