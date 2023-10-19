<?php

set('id', 1);
set('title', L::new." ".L::user);

echo html('users/_form.html.php', null, array('user' => $user, 'method' => 'POST', 'action' => url_for('users')));

?>
