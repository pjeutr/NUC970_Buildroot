<?php

set('id', 21);
set('title', L::new." ".L::holiday);

echo html('holidays/_form.html.php', null, array('holiday' => $holiday, 'method' => 'POST', 'action' => url_for('holidays')));

?>
