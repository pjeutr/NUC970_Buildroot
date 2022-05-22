#!/bin/sh

#mkyaffs2 --inband-tags maasland_app output/images/maasland_app.yaffs2

#LEB_size = PEB_size - ((Subpage_size + Page_size)) / Page_size) * Page_size
#LEB_size = 0x20000 - ((2048 + 2048)) / 2048) * 2048
#= 0x20000 - 4096 = 0x1F000 = 126976

#LEB size: 126976 bytes (124 KiB), min./max. I/O unit sizes: 2048 bytes/2048 bytes
#FS size: 98152448 bytes (93 MiB, 773 LEBs), journal size 9023488 bytes (8 MiB, 72 LEBs)
#mkfs.ubifs -F -d maasland_app -e 0x1F000 -c 808 -m 0x800 -o output/images/maasland_app.ubifs

#LEB size: 126976 bytes (124 KiB), min./max. I/O unit sizes: 2048 bytes/2048 bytes
#FS size: 93073408 bytes (88 MiB, 733 LEBs), journal size 9023488 bytes (8 MiB, 72 LEBs)

#rootfs
#LEB size: 126976 bytes (124 KiB), min./max. I/O unit sizes: 2048 bytes/2048 bytes
#FS size: 33 554 432 bytes (35 MiB, 733 LEBs), journal size 9023488 bytes (8 MiB, 72 LEBs)
# 32M => 33554432 / 126976(0x1F000) = -c 264 
# 31M => 32505856 / 126976(0x1F000) = 256 => -c 232
#mkfs.ubifs -d maasland_app -e 0x1F000 -c 232 -m 0x800 -o output/images/maasland_app.ubifs
mkfs.ubifs -F -d maasland_app -e 0x1F000 -c 1536 -m 0x800 -o output/images/maasland_app.ubifs
ubinize -o output/images/maasland_app.ubi -m 0x800 -p 0x20000 -s 2048 board/nuvoton/match/ubinize.cfg

# -e --leb-size=SIZE
# -c --max-leb-cnt=COUNT <--
# -m --min-io-size=SIZE

# -m --min-io-size=<bytes>
# -p --peb-size=<bytes>
# -s --sub-page-size=<bytes>

# ubiformat /dev/mtd3
# ubiattach -p /dev/mtd3
# ubimkvol /dev/ubi1 -N volume_name -s 32MiB
#Volume ID 0, size 265 LEBs (33648640 bytes, 32.1 MiB), LEB size 126976 bytes (124.0 KiB), dynamic, name "volume_name", alignment 1

