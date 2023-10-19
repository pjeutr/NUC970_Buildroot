<?php

set('id', 3);
set('title', L::edit." ".L::controller);

echo html('controllers/_form.html.php', null, array('controller' => $controller, 'method' => 'PUT', 'action' => url_for('controllers', $controller->id)));

?>
