#!/usr//bin/php
<?php

/*
* Show status of all outputs
*/

require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/limonade.php';;
require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/lib/logic.slave.php';

echo checkIfMaster() ? "Master" : "Slave";
echo "\nMaster=". getGPIO(GVAR::$GPIO_MASTER);
echo "\nFirmware=". getGPIO(GVAR::$GPIO_FIRMWARE);
echo "\nButton1=". getGPIO(GVAR::$GPIO_BUTTON1);
echo "\nButton2=". getGPIO(GVAR::$GPIO_BUTTON2);
echo "\nDoorstatus1=". getGPIO(GVAR::$GPIO_DOORSTATUS1);
echo "\nDoorstatus2=". getGPIO(GVAR::$GPIO_DOORSTATUS2);

