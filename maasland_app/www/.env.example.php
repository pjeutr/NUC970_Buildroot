<?php

/*
*   Enviroment file, copy this file to .env.php 
*   HARDWARE_VERSION = 1, old match version (different gpio)
*   Irritant controller buzzer let's you know. OUT12V and BUZZER where swapped. To turn it off on:
*   echo 0 >/sys/class/gpio/gpio138/value MATCH4
*   echo 0 >/sys/class/gpio/gpio79/value MATCH2
*   APP_LOG_LEVEL 1=info 2=debug 3=error
*/

return [
    'APP_DEBUG' => true,
    'APP_DEVELOPMENT' => false,
    'APP_LOG_LEVEL' => 1,
    'HARDWARE_VERSION' => 2, 
];


