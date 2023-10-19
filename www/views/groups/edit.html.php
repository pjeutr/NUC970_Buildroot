<?php

set('id', 2);
set('title', L::edit." ".L::group);

echo html('groups/_form.html.php', null, array('group' => $group, 'method' => 'PUT', 'action' => url_for('groups', $group->id)));

?>
