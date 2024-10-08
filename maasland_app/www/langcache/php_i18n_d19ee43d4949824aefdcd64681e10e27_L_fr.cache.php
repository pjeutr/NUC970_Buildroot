<?php class L {
const language = 'Langue';
const user = 'utilisateur';
const group = 'groupe';
const door = 'porte';
const holiday = 'Vacances';
const timezone = 'fuseaux horaire';
const report = 'rapport';
const setting_door_open = 'Durée d\'ouverture de la porte (en secondes)';
const setting_clean_reports = 'Reports entries removed after (in days)';
const setting_hostname = 'Nom d\'hôte';
const setting_password = 'Mot de passe';
const setting_admin_password = 'Admin Password';
const setting_timezone = 'Timezone';
const setting_alarm = 'Alarm (en secondes)';
const setting_upload = 'Télécharger la configuration';
const setting_download = 'Télécharger la configuration';
const setting_ledger = 'Custom mode';
const setting_time = 'Date Time';
const controller = 'contrôleur';
const ledger = 'Attendancelist';
const holidays = 'Vacances';
const presence = 'Presence';
const time_in = 'Time in';
const time_out = 'Time out';
const open = 'Ouvrir';
const close = 'Fermer';
const edit = 'Modifier';
const add = 'Ajouter';
const new = 'Nouveau';
const users = 'Utilisateurs';
const groups = 'Groupes';
const doors = 'Portes';
const timezones = 'Fuseaux horaires';
const reports = 'Rapports';
const settings = 'Paramètres';
const controllers = 'Contrôleurs';
const network = 'Network';
const id = 'id';
const name = 'nom';
const online = 'online';
const ip = 'ip';
const key = 'code clé';
const time = 'Temps';
const key_sub = 'Entrer un code';
const key_button = 'Utiliser une clé scannée';
const key_button_remark = 'Scan a key and press this button';
const choose_file = 'Choisir le fichier';
const key_remark = 'Tapez le code ou le code W26 de balise';
const generic_sub = 'Saisissez un ';
const visits = 'Visites';
const lastseen = 'Dernière vue';
const action = 'action';
const startdate = 'date d\'entrée en vigueur';
const startdate_remark = 'Avant cette date, le code/la balise n\'est pas valable (vide pour toujours).';
const enddate = 'date de fin';
const enddate_remark = 'Après cette date, le code/la balise n\'est pas valable (vide pour toujours)';
const maxvisits = 'nombre maximum de visites';
const maxvisits_remark = 'Après le nombre maximum de visites, la balise/le code n\'est plus valable (le vide est illimité)';
const reset_visits = 'Reset visits';
const reset_visits_remark = 'Reset the number of visits back to zero';
const inactive = 'Temporarily disable access';
const remarks = 'remarques';
const remarks_sub = 'Espace pour les remarques';
const search_controller_button = 'Recherche de contrôleurs';
const search_controller_remark = 'Recherchez et sélectionnez un contrôleur pour remplir les champs ci-dessous';
const networkaddress = 'Adresse du réseau';
const networkaddress_remark = 'Press search to find an ip address';
const choose = 'Choisissez...';
const door_timezone_button_info = 'Remove timezone and close door';
const door_timezone_button = 'Remove timezone';
const timezone_warning = 'Attention! La porte s\'ouvrira automatiquement à la fuseaux horaires';
const timezone_remark = 'La porte s\'ouvrira automatiquement à la fuseaux horaires';
const start = 'Commencer';
const end = 'Fin';
const weekdays = 'Jours ouvrables';
const weekdays2 = 'Jours de la semaine';
const value = 'Valeurs';
const apb = 'APB';
const apb_info = 'Use this controller for APB';
const term_reader = 'Lecteur';
const term_button = 'Boutons-poussoir';
const term_sensor = 'Sensor';
const button_new = 'Nouveau';
const button_edit = 'Modifier';
const button_change = 'Changez';
const button_delete = 'Supprimer';
const button_confirm = 'YOui, supprimez-le !';
const button_cancel = 'Annuler';
const button_save = 'Sauver';
const button_newrule = 'Nouvelle règle';
const button_downloadcsv = 'Télécharger csv';
const button_logout = 'Se déconnecter';
const button_replicate = 'Duplicate';
const button_replicate_tip = 'Copy config to all controllers';
const delete_confirm = 'Vous êtes sûr ?';
const delete_subtext = 'Cet élément sera supprimé !';
const message_no_auth = 'You have no authorisation to use this page';
const message_slave = 'Il s\'agit d\'un contrôleur esclave';
const message_factoryreset = 'L\'interrupteur de réinitialisation des paramètres d\'usine est activé.<br>Les paramètres d\'usine ont été remis en place et l\'ancienne configuration est supprimée.';
const message_db_error = 'Quelque chose s\'est mal passé avec la configuration.<br> Essayez de réinitialiser les paramètres d\'usine, en utilisant le commutateur approprié.';
const message_unkown_error = 'Quelque chose a mal tourné';
const message_changes_saved = 'The changes were saved!';
const message_success_title = 'Great';
const message_error_title = 'Oops';
const message_visitreset = 'The number of visits of user %s has been set to 0';
const message_no_master_found = 'No master controller found! <br> You can change jumpers and make this controller the master.';
const message_ledger_in = 'User is present in the building';
const message_ledger_out = 'User is not in the building';
const warning_change_unreachable = '<b> Warning! - </b>  Changes on this page, can make the system unreachable!';
const warning_change_network = '<b>Before changing network settings on Master.<br>First manage network settings on slaves:</b>';
const dashboard_name = 'Tableau de bord';
const dashboard_buttons = 'Envoyer la porte ouverte';
const dashboard_title = 'Ce contrôleur a %s';
const dashboard_text1 = '<div class=\'typography-line\'><p>
<span>Hardware</span></p><ul>
<li>2 sorties relais - pour se connecter aux serrures de portes</li>
<li>2 entrées de Wiegand - pour se connecter au clavier à code ou au lecteur de badges</li>
<li>2 sorties d\'alarme - pour se connecter aux alarmes</li>
<li>2 sortie de signalisation - pour se connecter aux signalisations de porte</li>
<li>Connecteur UTP - pour se connecter à un LAN</li>
<li>Voltage en - pour connecter 8-24VDC</li>
</ul><p></p></div>';
const dashboard_text2 = '<div class=\'typography-line\'>
<span>Configuration</span><ol>
<li>Ajouter des portes à partir de ce contrôleur Maître ou d\'autres contrôleurs Esclaves.</li>
<li>Ajouter des fuseaux horaires (24h et les heures de travail sont prédéfinies)</li>
<li>Créer des groupes avec des fuseaux horaires</li>
<li>Créer des utilisateurs et les affecter à un groupe</li>
<li>Ajouter un code ou une balise à l\'utilisateur</li>
</ol></div>';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}