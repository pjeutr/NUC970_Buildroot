#!/bin/sh

# wget https://maaslandserver.com/firmware/matchSDv1_7_2.img

sudo losetup -fP sdcard/matchSDvx_x_x.img
sudo mount /dev/loop0p1 sdcard/mount
sudo cp output/images/maasland_app.ubi sdcard/mount/
echo "sudo mv sdcard/mount/_matchSDv1_7_2 sdcard/mount/_matchSDv1_7_3"
sudo ls sdcard/mount/_matchSDv*

#sudo umount /dev/loop0p1 
#sudo losetup -d /dev/loop0
#losetup --list

#curl -k --upload-file matchSDvx_x_x.img https://free.keep.sh

echo "cd /home/maasland/plik/server/ \n ./plikd"
