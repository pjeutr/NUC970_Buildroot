#!/bin/bash

while inotifywait -e modify /var/log/messages; do
#while inotifywait -e modify /sys/kernel/wiegand/read; do
  if tail -n1 /var/log/messages | grep keycode; then
    echo "KEEEEEEEY!"
    #/usr/bin/php /maasland_app/scripts/match_write.php
    wget -q -O - "http://192.168.178.137/?/door/1" 
  fi
done
	

