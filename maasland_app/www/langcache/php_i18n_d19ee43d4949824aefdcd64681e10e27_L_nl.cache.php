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
const setting_alarm = 'Hostnaam';
const setting_upload = 'Configuratie uploaden';
const setting_download = 'Configuratie downloaden';
const controller = 'controller';
const edit = 'Bewerken';
const add = 'Toevoegen';
const new = 'Nieuw';
const users = 'Gebruikers';
const groups = 'Groepen';
const doors = 'Deuren';
const timezones = 'Tijdzones';
const reports = 'Rapporten';
const settings = 'Instellingen';
const controllers = 'Controllers';
const id = 'id';
const name = 'naam';
const key = 'sleutelcode';
const time = 'Tijd';
const key_sub = 'Code invoeren';
const key_button = 'Gebruik een gescande sleutel';
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
const remarks = 'opmerkingen';
const remarks_sub = 'Ruimte voor opmerkingen';
const search_controller_button = 'Zoeken naar controllers';
const search_controller_remark = 'Zoek en selecteer een controller om de onderstaande velden te vullen';
const networkaddress = 'Netwerkadres';
const choose = 'Kies...';
const timezone_warning = 'Let op! De deur zal automatisch openen op de gekozen tijdzone';
const timezone_remark = 'De deur zal automatisch openen op de gekozen tijdzone
Annuleren';
const start = 'Start';
const end = 'Einde';
const weekdays = 'Werkdagen';
const weekdays2 = 'Dagen van de week';
const value = 'Waarden';
const button_new = 'Nieuw';
const button_edit = 'Veranderen';
const button_change = 'Wijzigen';
const button_delete = 'Verwijderen';
const button_confirm = 'Ja, verwijder het!';
const button_cancel = 'Annuleren';
const button_save = 'Opslaan';
const button_newrule = 'New regel';
const button_downloadcsv = 'Download csv';
const button_logout = 'Log uit';
const delete_confirm = 'Weet u het zeker?';
const delete_subtext = 'Dit item zal worden verwijderd!';
const message_slave = 'Dit is een slave controller';
const message_factoryreset = 'De reset fabrieksinstellingen schakelaar staat aan.<br>Fabrieksinstellingen werden teruggezet en de oude configuratie werd gewist';
const message_db_error = 'Er is iets misgegaan met de configuratie.<br> Probeer de fabrieksinstellingen te resetten, door de juiste schakelaar te gebruiken.';
const message_unkown_error = 'Er is iets misgegaan';
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