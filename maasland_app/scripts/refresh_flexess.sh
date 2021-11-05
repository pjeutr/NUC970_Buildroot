#!/bin/sh
#

/etc/init.d/S60flexess stop
killall inotifywait && killall epoll_userspace && killall avahi-publish-service
/etc/init.d/S60flexess start


