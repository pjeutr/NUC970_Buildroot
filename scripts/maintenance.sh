#!/bin/sh
#
# Script to be called from cron at night, or other low activity moment 
#
# 0 4   *   *   *	/maasland_app/scripts/maintenance.sh
#

action=false
action=true 
if [ "$action" = true ]; then
	logger "INFO do fresh up"
	#/sbin/reboot
	#restart flexess if memory is to low
	if [ $(awk '/^MemFree:/ { print $2; }' /proc/meminfo) -lt 1999 ]; then
	    logger "WARNING:low memory restart application"
	    /etc/init.d/S60flexess restart
	fi
fi


