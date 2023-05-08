#!/usr/bin/php
<?php

/*
* Show data for analysis
*/

require_once '/maasland_app/www/lib/helpers.php';
#require_once '/maasland_app/vendor/autoload.php';
#require_once '/maasland_app/www/lib/limonade.php';
#require_once '/maasland_app/www/lib/logic.slave.php';
#require_once '/maasland_app/vendor/pjeutr/dotenv-php/src/DotEnv.php';

// require_once '/maasland_app/www/lib/db.php';
// require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/db/gvar.match4.php';

$now = new DateTime(getTimezone());
echo "\nApplication time:".$now->format('D, d M Y H:i:s O T')." tz=".getTimezone();
echo "\n";
echo "\nSoftware=v". GVAR::$DASHBOARD_VERSION."  ";
//mylog("analysis done");