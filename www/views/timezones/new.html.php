<?php

set('id', 4);
set('title', L::new." ".L::timezone);

echo html('timezones/_form.html.php', null, array('timezone' => $timezone, 'method' => 'POST', 'action' => url_for('timezones')));

?>
