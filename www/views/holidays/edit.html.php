<?php

set('id', 21);
set('title', L::edit." ".L::holiday);

echo html('holidays/_form.html.php', null, array('holiday' => $holiday, 'method' => 'PUT', 'action' => url_for('holidays', $holiday->id)));

?>
