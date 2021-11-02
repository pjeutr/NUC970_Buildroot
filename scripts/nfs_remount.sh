#!/bin/sh
#
# Replace DuoApp with a nfs drive
#
# https://www.thegeekdiary.com/common-nfs-mount-options-in-linux/
# https://man7.org/linux/man-pages/man8/mount.8.html#FILESYSTEM-INDEPENDENT_MOUNT_OPTIONS

/etc/init.d/S49php-fpm stop
/etc/init.d/S50lighttpd stop
/etc/init.d/S60flexess stop
umount maasland_app
mount -t nfs -o port=2049,nolock,proto=tcp,rw,suid ubu:/home/pjeutr/nuvoton/DuoApp /maasland_app
#mount -o port=2049,nolock,proto=tcp ubu:/home/pjeutr/nuvoton/DuoApp /maasland_app
/etc/init.d/S49php-fpm start
/etc/init.d/S50lighttpd start
/etc/init.d/S60flexess start

