<?php

set('id', 7);
set('title', 'Edit Timezone');

echo html('timezones/_form.html.php', null, array('timezone' => $timezone, 'method' => 'PUT', 'action' => url_for('timezones', $timezone->id)));

?>
