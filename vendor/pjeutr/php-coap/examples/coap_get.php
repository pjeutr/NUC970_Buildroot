<?php

require('/maasland_app/vendor/pjeutr/php-coap/src/PhpCoap/functions.php');

$loop = React\EventLoop\Factory::create();

$client = new PhpCoap\Client( $loop );

$client->get( 'coap://192.168.178.165/status/68', function( $data ) {
	var_dump( json_decode( $data ));
} );

$loop->run();

?>