tail -f /var/log/lighttpd-error.log
/etc/init.d/S50sshd restart
/etc/init.d/S50lighttpd restart
ls -la /maasland_app/
avahi-browse -tpr _maasland._tcp 
avahi-browse -tpr _master._sub._maasland._udp
/etc/init.d/S60flexess restart
/maasland_app/tests/status.php
tail -f /var/log/php_errors.log


