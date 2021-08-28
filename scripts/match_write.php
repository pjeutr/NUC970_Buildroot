#!/usr//bin/php
<?php


$filename = "/sys/kernel/wiegand/read";

$rawContents = file_get_contents($filename);
//dissect the input
$content = explode(":",$rawContents);
$nr = $content[0];
$keycode = $content[1];
$reader = $content[2];
$raw = $content[3];

echo "Activity nr:key:reader:raw:switch ".$nr.":".$keycode.":".$reader."\n";





