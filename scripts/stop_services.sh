#!/bin/sh
#
# Replace DuoApp with the latest version from github
#

/etc/init.d/S49php-fpm stop
/etc/init.d/S50lighttpd stop
/etc/init.d/S50avahi-daemon stop
/etc/init.d/S49ntp stop

