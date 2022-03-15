#!/bin/sh
#

/etc/init.d/S60flexess stop
/etc/init.d/S50lighttpd stop
/etc/init.d/S49php-fpm stop

/etc/init.d/S49php-fpm start
/etc/init.d/S50lighttpd start
/etc/init.d/S60flexess start


