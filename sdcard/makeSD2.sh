#!/bin/sh

sudo umount /dev/loop0p1 
sudo losetup -d /dev/loop0
losetup --list

#curl -k --upload-file sdcard/matchSDvx_x_x.img https://free.keep.sh
curl --form 'file=@sdcard/matchSDvx_x_x.img' http://mslnd.nl:8080

#echo "curl -L https://free.keep.sh/xxx/php_errors.log > php_errors.log"
echo "curl -O http://mslnd.nl:8080/file/lLG55FITRCbhQ46d/uE1sia3aohJpjoyH/matchSDvx_x_x.img"
echo "mv matchSDvx_x_x.img ~/maaslandserver/public/firmware/matchSDv1_8_8.img"
