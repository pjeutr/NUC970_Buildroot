<?php

set('id', 2);
set('title', 'New Group');

echo html('groups/_form.html.php', null, array('group' => $group, 'method' => 'POST', 'action' => url_for('groups')));

?>
