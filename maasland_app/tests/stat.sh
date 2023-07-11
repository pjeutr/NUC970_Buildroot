#!/bin/bash

#$1 is a parameter given to get more information

#get local ip adress
echo ip=$(ifconfig eth0 | awk '/inet addr/{print substr($2,6)}') 
echo $(uptime)
#print the heaviest tasks
#ps -o pid,user,vsz,rss,comm,args | grep -v ]$ | awk '{if($4>1000) print $0}'  | sort -n -k3

mpstat | grep -v Linux
echo
free -m | grep -v Swap
ifconfig eth0

#$1 greater than 100, get statistical info that take time to gather 
if [ $1 -gt 100 ]
then

sar 2 3 | grep -v Linux
sar -r 2 3 | grep -v Linux
printf "\n\nIFACE   rxerr/s   txerr/s    coll/s  rxdrop/s  txdrop/s  txcarr/s  rxfram/s  rxfifo/s  txfifo/s"
sar -n DEV 1 3 | grep eth0 | grep -v Linux 
sar -n SOCK 1 3 | grep eth0 | grep -v Linux 
sar -w 2 3 | grep -v Linux

fi

#$1 greater than 10, get info for individual processes
if [ $1 -gt 10 ] 
then 

tail -f /var/log/php_errors.log

pidstat -ulrwh | sort -n -k12 | grep -v Linux

fi

