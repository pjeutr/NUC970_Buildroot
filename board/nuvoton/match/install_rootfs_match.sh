#!/bin/sh

#TOD replace cd with
BOARD_DIR="$(dirname $0)"
BOARD_NAME="$(basename ${BOARD_DIR})"
# ${BOARD_DIR}/matcht2_test/drv/make

#Make Wang test app
# cd board/nuvoton/match/match2_test/drv
# make 
# cd ../user
# make
# cd uart4\&5
# make

#Back to basedir
#cd ../../../../../../

cd board/nuvoton/match/at24mac
arm-linux-gcc at24mac.c -o at24mac 
arm-linux-gcc at24serial.c -o at24serial 
make
cd ../../../../
cp -af board/nuvoton/match/at24mac/at24mac overlay/scripts

#cd board/nuvoton/match/simpleWiegandReader
cd board/nuvoton/match/wiegand-driver
arm-linux-gcc epoll_userspace.c -o epoll_userspace
make
cd ../../../../

cd board/nuvoton/match/duo-module
arm-linux-gcc duo-user.c -o duo-user
arm-linux-gcc poll_userspace.c -o poll_userspace
arm-linux-gcc epoll_userspace.c -o epoll_userspace
make
cd ../../../../

#cd board/nuvoton/match/simpleWiegandReader
cd board/nuvoton/match/sysfs-poll
arm-linux-gcc sysfs-select-user.c -o sysfs-select
arm-linux-gcc sysfs-poll-user.c -o sysfs-poll
make

cd ../../../../

#cp files to the overlay
#cp -af board/nuvoton/match/match2_test/drv/test_drv.ko overlay/scripts
#cp -af board/nuvoton/match/match2_test/user/test_app overlay/scripts
#cp -af board/nuvoton/match/match2_test/user/uart4\&5/uart_test overlay/scripts
cp -af board/nuvoton/match/wiegand-driver/wiegand-driver.ko overlay/scripts
cp -af board/nuvoton/match/wiegand-driver/epoll_userspace overlay/scripts
#if [ -d $APP_PATH ]; then
#	cp $APP_PATH/yaffs2utils/mkyaffs2 output/target/usr/bin/
#	cp $APP_PATH/yaffs2utils/unyaffs2 output/target/usr/bin/
#	cp $APP_PATH/yaffs2utils/unspare2 output/target/usr/bin/
#fi

#replace persistent config files by a symlink 
#rm output/target/etc/network/interfaces
#ln -s ../../maasland_app/etc/interfaces output/target/etc/network/interfaces
