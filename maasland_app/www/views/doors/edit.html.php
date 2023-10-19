<?php

set('id', 3);
set('title', L::edit." ".L::door);

echo html('doors/_form.html.php', null, array('door' => $door, 'method' => 'PUT', 'action' => url_for('doors', $door->id)));

?>
