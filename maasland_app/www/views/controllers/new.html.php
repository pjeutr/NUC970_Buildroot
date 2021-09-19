<?php

set('id', 1);
set('title', 'New Controller');

echo html('controllers/_form.html.php', null, array('controller' => $controller, 'method' => 'POST', 'action' => url_for('controllers')));

?>
