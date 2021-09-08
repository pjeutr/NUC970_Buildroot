#!/bin/sh
echo -e "Content-type: text/html\r\n\r\n";
echo -e `/sbin/ifconfig eth0 | grep 'inet addr'`;
