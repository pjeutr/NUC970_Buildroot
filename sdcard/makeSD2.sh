#!/bin/sh

sudo umount /dev/loop0p1 
sudo losetup -d /dev/loop0
losetup --list

curl -k --upload-file sdcard/matchSDvx_x_x.img https://free.keep.sh

echo "curl -L https://free.keep.sh/xxx/php_errors.log > php_errors.log"
