<?php class L {
const language = 'Taal';
const user = 'gebruiker';
const group = 'groep';
const door = 'deur';
const timezone = 'tijdzone';
const report = 'rapport';
const setting_door_open = 'Deur open tijd (in seconden)';
const setting_sound_buzzer = 'Zoemer klinkt wanneer deur wordt geopend';
const setting_hostname = 'Hostnaam';
const setting_password = 'Password';
const setting_apb = 'Anti-passback(in seconden)';
const setting_alarm = 'Alarm (in seconds)';
const setting_upload = 'Configuratie uploaden';
const setting_download = 'Configuratie downloaden';
const setting_ledger = 'Aanwezigheid bijhouden';
const controller = 'controller';
const ledger = 'Aanwezigheidslijst';
const presence = 'Aanwezig';
const time_in = 'Tijd in';
const time_out = 'Tijd uit';
const ledger_in = 'Gebruiker bevindt zich in het gebouw ';
const ledger_out = 'Gebruiker is niet in het gebouw';
const open = 'Open';
const close = 'Dicht';
const edit = 'Wijzigen';
const add = 'Toevoegen';
const new = 'Nieuwe';
const users = 'Gebruikers';
const groups = 'Groepen';
const doors = 'Deuren';
const timezones = 'Tijdzones';
const reports = 'Logboek';
const settings = 'Instellingen';
const controllers = 'Controllers';
const id = 'id';
const name = 'naam';
const online = 'online';
const ip = 'ip';
const key = 'pasnummer';
const time = 'Tijd';
const key_sub = 'Code invoeren';
const key_button = 'Gebruik laatst gescande pas';
const key_button_remark = 'Gebruik een pas en druk op deze knop';
const choose_file = 'Kies bestand';
const key_remark = 'Typ de pincode of de W26-code van de tag';
const generic_sub = 'Voer in ';
const visits = 'Bezoeken';
const lastseen = 'Laatst gezien';
const action = 'actie';
const startdate = 'begindatum';
const startdate_remark = 'Voor deze datum is de code/tag ongeldig (leeg is voor altijd)';
const enddate = 'einddatum';
const enddate_remark = 'Na deze datum is de code/tag ongeldig (leeg is voor altijd)';
const maxvisits = 'maximale aantal bezoeken';
const maxvisits_remark = 'Na het maximum aantal bezoeken is de tag/code ongeldig (leeg is onbeperkt)';
const reset_visits = 'Reset visits';
const reset_visits_remark = 'Zet het aantal bezoeken terug naar nul';
const inactive = 'Trek toegang tijdelijk in';
const remarks = 'opmerkingen';
const remarks_sub = 'Ruimte voor opmerkingen';
const search_controller_button = 'Zoeken naar controllers';
const search_controller_remark = 'Zoek en selecteer een controller om de onderstaande velden te vullen';
const networkaddress = 'Netwerkadres';
const networkaddress_remark = 'Druk op zoeken om een ip address te vinden';
const choose = 'Kies...';
const timezone_warning = 'Let op! De deur zal automatisch openen op de gekozen tijdzone';
const timezone_remark = 'De deur zal automatisch openen op de gekozen tijdzone (Het kan een minuut duren voordat een verandering is doorgevoerd)';
const start = 'Start';
const end = 'Einde';
const weekdays = 'Dagen';
const weekdays2 = 'Dagen van de week';
const value = 'Waarden';
const term_reader = 'Lezer';
const term_button = 'Drukknop';
const term_sensor = 'Sensor';
const button_new = 'Nieuwe';
const button_edit = 'Wijzigen';
const button_change = 'Wijzigen';
const button_delete = 'Verwijderen';
const button_confirm = 'Ja, verwijder het!';
const button_cancel = 'Annuleren';
const button_save = 'Opslaan';
const button_newrule = 'New regel';
const button_downloadcsv = 'Download csv';
const button_logout = 'Uitloggen';
const delete_confirm = 'Weet u het zeker?';
const delete_subtext = 'Dit item zal worden verwijderd!';
const message_slave = 'Dit is een slave controller';
const message_factoryreset = 'De reset fabrieksinstellingen schakelaar staat aan.<br>Fabrieksinstellingen werden teruggezet en de oude configuratie werd gewist';
const message_db_error = 'Er is iets misgegaan met de configuratie.<br> Probeer de fabrieksinstellingen te resetten, door de juiste schakelaar te gebruiken.';
const message_unkown_error = 'Er is iets misgegaan';
const message_changes_saved = 'De verandering zijn bewaard!';
const message_success_title = 'Mooi';
const message_error_title = 'Oops';
const message_visitreset = 'Het aantal bezoeken van gebruiker %s zijn terug gezet op 0';
const message_no_master_found = 'No master controller found! <br> You can change jumpers and make this controller the master.';
const dashboard_name = 'Dashboard';
const dashboard_buttons = 'Deur open sturen';
const dashboard_title = 'Deze controller heeft %s';
const dashboard_text1 = '<div class=\'typography-line\'><p>
<span>Hardware</span></p><ul>
<li>2 relais uitgangen - om de deursloten aan te sluiten</li>
<li>2 Wiegand ingangen - om een codeslot of kaartlezer aan te sluiten</li>
<li>2 alarm uitgangen - om het alarm aan te sluiten</li>
<li>2 signaleringsingangen - om de signaleringen aan te sluiten</li>
<li>UTP aansluiting - voor een LAN verbinding</li>
<li>Voltage in - om 8-24VDC aan te sluiten</li>
</ul><p></p></div>';
const dashboard_text2 = '<div class=\'typography-line\'>
<span>Configuratie</span><ol>
<li>Voeg deuren toe van deze Master controller, of van andere Slave controllers</li>
<li>Tijdzones toevoegen (24 uur en werkuren zijn vooraf gedefinieerd)</li>
<li>Groepen met tijdzones maken</li>
<li>Maak gebruikers aan en wijs ze toe aan een groep</li>
<li>Voeg een code of tag toe aan de gebruiker</li>
</ol></div>';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}