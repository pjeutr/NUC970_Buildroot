<?php

#include __DIR__.'/../vendor/autoload.php';
#include '/Volumes/Work/repositories/test/vendor/autoload.php';
include '/maasland_app/vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
#$loop = React\EventLoop\Factory::create();

$observer = new \Calcinai\Rubberneck\Observer($loop);

$observer->onModify(function($file_name){
    echo "Modified: $file_name\n";
});

$observer->onCreate(function($file_name){
    echo "Created: $file_name\n";
});

$observer->onDelete(function($file_name){
    echo "Deleted: $file_name\n";
});


$observer->watch('/sys/kernel/wiegand/read');
$observer->watch('/sys/class/gpio/gpio170/value');
$observer->watch('/sys/class/gpio/gpio68/value');

$loop->run();

