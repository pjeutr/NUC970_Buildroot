#!/bin/sh

#mkyaffs2 --inband-tags maasland_app output/images/maasland_app.yaffs2

#LEB_size = PEB_size - ((Subpage_size + Page_size)) / Page_size) * Page_size
#LEB_size = 0x20000 - ((2048 + 2048)) / 2048) * 2048
#= 0x20000 - 4096 = 0x1F000

#LEB size: 126976 bytes (124 KiB), min./max. I/O unit sizes: 2048 bytes/2048 bytes
#FS size: 98152448 bytes (93 MiB, 773 LEBs), journal size 9023488 bytes (8 MiB, 72 LEBs)
#mkfs.ubifs -F -d maasland_app -e 0x1F000 -c 808 -m 0x800 -o output/images/maasland_app.ubifs

#LEB size: 126976 bytes (124 KiB), min./max. I/O unit sizes: 2048 bytes/2048 bytes
#FS size: 93073408 bytes (88 MiB, 733 LEBs), journal size 9023488 bytes (8 MiB, 72 LEBs)
mkfs.ubifs -F -d maasland_app -e 0x1F000 -c 768 -m 0x800 -o output/images/maasland_app.ubifs
ubinize -o output/images/maasland_app.ubi -m 0x800 -p 0x20000 -s 2048 board/nuvoton/match/ubinize.cfg

# -e --leb-size=SIZE
# -c --max-leb-cnt=COUNT
# -m --min-io-size=SIZE

# -m --min-io-size=<bytes>
# -p --peb-size=<bytes>
# -s --sub-page-size=<bytes>
