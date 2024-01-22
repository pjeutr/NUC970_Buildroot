#!/bin/sh
#
# Script to be called from Cron
#
# */1 * * * * /maasland_app/scripts/flexess_cron.sh
#

# Look for a running instance 
p=$(ps -a | grep "[i]nputListener\.php")
# Get the second item; the process number
n=$(echo $p | awk '{print $1}')
# If it's not empty, kill the process
if ! [ "$n" ]
then
	logger "WARNING:flexess listener not running, doing a restart!"
	/etc/init.d/S60flexess restart
fi

# #log load if it's to big
# load=$(uptime | awk '{print $6}' | cut -d "," -f 1)
# warn=0.01
# logger $load is $warn 
# if [ "$load" -gt "$warn" ]; then
#     logger "WARNING:HEAVY LOAD!"
# fi

#log memory if it's to low
#restart happens in maintenance.sh which is called once a day
if [ $(awk '/^MemFree:/ { print $2; }' /proc/meminfo) -lt 1999 ]; then
    logger "WARNING:low memory"
    logger `grep Mem /proc/meminfo` 
fi