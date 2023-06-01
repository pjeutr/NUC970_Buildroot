#!/bin/sh
#
# Script to be called from Cron
#
# 0 4 * * *	/maasland_app/scripts/maintenance.sh
#

action=false
action=true
if [ "$action" = true ]; then
	logger "INFO do fresh up"
	#/sbin/reboot
fi


