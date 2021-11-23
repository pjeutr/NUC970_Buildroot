tail -f /var/log/php_errors.log
tail -f /var/log/lighttpd-error.log
/etc/init.d/S50sshd restart
/etc/init.d/S50lighttpd restart
ls -la /maasland_app/
avahi-browse -tpr _maasland._tcp 


