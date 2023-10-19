<?php

set('id', 7);
set('title', 'New Timezone');

echo html('timezones/_form.html.php', null, array('timezone' => $timezone, 'method' => 'POST', 'action' => url_for('timezones')));

?>
