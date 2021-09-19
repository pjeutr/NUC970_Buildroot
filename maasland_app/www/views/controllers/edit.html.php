<?php

set('id', 1);
set('title', 'Edit Controller');

echo html('controllers/_form.html.php', null, array('controller' => $controller, 'method' => 'PUT', 'action' => url_for('controllers', $controller->id)));

?>
