<?php

set('id', 4);
set('title', L::edit." ".L::timezone);

echo html('timezones/_form.html.php', null, array('timezone' => $timezone, 'method' => 'PUT', 'action' => url_for('timezones', $timezone->id)));

?>
