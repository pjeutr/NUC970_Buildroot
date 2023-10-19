#!/usr//bin/php
<?php
//require( __DIR__ . '/vendor/autoload.php' );
require_once '/maasland_app/vendor/autoload.php';

//$loop = React\EventLoop\Loop::get();
$loop = React\EventLoop\Factory::create();
echo date("H:i:s").' start'. PHP_EOL;
$timer = $loop->addPeriodicTimer(1, function () {
    echo 'tick!' . PHP_EOL;
});

$loop->addTimer(3, function () use ($loop, $timer) {
    $loop->cancelTimer($timer);
    echo date("H:i:s").'Done' . PHP_EOL;
});

$loop->run();