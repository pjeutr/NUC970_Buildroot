#!/bin/bash

#awk '/inet addr/ {gsub(\"addr:\", \"\", $2); print $2}'


echo CHECK myip
ifconfig eth0 | awk '/inet addr/{print substr($2,6)}'
hostname -i

echo Master:
avahi-browse -tpr _master._sub._maasland._udp | awk -F';' '$1 - /^=/ {print $7" ip="$8}'

echo Slaves:
avahi-browse -tpr _maasland._udp | awk -F';' '$1 - /^=/ {print $7" ip="$8}'

# echo CHECK _master._sub._maasland._udp
# avahi-browse -tpr _master._sub._maasland._udp 

# echo CHECK _maasland._udp
# avahi-browse -tpr _maasland._udp

# echo CHECK _maasland._tcp
# avahi-browse -tpr _maasland._tcp