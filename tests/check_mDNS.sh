#!/bin/bash

echo CHECK _master._sub._maasland._udp
avahi-browse -tpr _master._sub._maasland._udp 

echo CHECK _maasland._udp
avahi-browse -tpr _maasland._udp

echo CHECK _maasland._tcp
avahi-browse -tpr _maasland._tcp



