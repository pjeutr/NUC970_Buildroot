<?php

set('id', 2);
set('title', L::new." ".L::group);

echo html('groups/_form.html.php', null, array('group' => $group, 'method' => 'POST', 'action' => url_for('groups')));

?>
