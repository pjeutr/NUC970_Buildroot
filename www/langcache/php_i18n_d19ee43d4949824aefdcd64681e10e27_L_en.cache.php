<?php class L {
const language = 'Language';
const user = 'user';
const group = 'access group';
const door = 'door';
const timezone = 'timezone';
const holiday = 'holiday';
const report = 'report';
const setting_door_open = 'Door open duration (in seconds)';
const setting_clean_reports = 'Reports entries removed after (in days)';
const setting_hostname = 'Hostname';
const setting_password = 'Password';
const setting_admin_password = 'Admin Password';
const setting_apb = 'Anti-passback(in seconds)';
const setting_alarm = 'Alarm (in seconds)';
const setting_upload = 'Upload configuration';
const setting_download = 'Download configuration';
const setting_ledger = 'Custom mode';
const setting_time = 'Date Time';
const setting_replicate = 'Replicate';
const controller = 'controller';
const ledger = 'Attendance list';
const holidays = 'Holidays';
const presence = 'Presence';
const time_in = 'Time in';
const time_out = 'Time out';
const open = 'Open';
const close = 'Close';
const edit = 'Edit';
const add = 'Add';
const new = 'New';
const users = 'Users';
const groups = 'Access groups';
const doors = 'Doors';
const timezones = 'Timezones';
const reports = 'Reports';
const settings = 'Settings';
const controllers = 'Controllers';
const network = 'Network';
const id = 'id';
const name = 'name';
const online = 'online';
const ip = 'ip';
const key = 'keycode';
const time = 'Time';
const key_sub = 'Enter a code';
const key_button = 'Use scanned key';
const key_button_remark = 'Scan a key and press this button';
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
const reset_visits = 'Reset visits';
const reset_visits_remark = 'Reset the number of visits back to zero';
const inactive = 'Temporarily disable access';
const remarks = 'remarks';
const remarks_sub = 'Space for remarks';
const search_controller_button = 'Search for controllers';
const search_controller_remark = 'Search and select a controller to fill the fields below';
const networkaddress = 'Network address';
const networkaddress_remark = 'Press search to find an ip address';
const choose = 'Choose...';
const door_timezone_button_info = 'Remove timezone and close door';
const door_timezone_button = 'Remove timezone';
const timezone_warning = 'Attention! The door will automatically open at chosen timezone';
const timezone_remark = 'The door will automatically open at chosen timezone (Changes can take a minute to propagate)';
const start = 'Start';
const end = 'End';
const weekdays = 'Weekdays';
const weekdays2 = 'Days of the week';
const value = 'Value';
const term_reader = 'Reader';
const term_button = 'Button';
const term_sensor = 'Sensor';
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
const message_changes_saved = 'The changes were saved!';
const message_success_title = 'Great';
const message_error_title = 'Oops';
const message_visitreset = 'The number of visits of user %s has been set to 0';
const message_no_master_found = 'No master controller found! <br> You can change jumpers and make this controller the master.';
const message_ledger_in = 'User is present in the building';
const message_ledger_out = 'User is not in the building';
const warning_change_unreachable = '<b> Warning! - </b>  Changes on this page, can make the system unreachable!';
const warning_change_network = '<b>Before changing network settings on Master.<br>First manage network settings on slaves:</b>';
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