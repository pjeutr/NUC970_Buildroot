<?php

set('id', 3);
set('title', L::new." ".L::controller);

echo html('controllers/_form.html.php', null, array('controller' => $controller, 'method' => 'POST', 'action' => url_for('controllers')));

?>
