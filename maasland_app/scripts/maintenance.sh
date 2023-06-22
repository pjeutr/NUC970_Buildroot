#!/bin/sh
#
# Script to be called from Cron
#
# 0 4   *   *   *	/maasland_app/scripts/maintenance.sh
#
# 0 4   *   *   *	/sbin/reboot
# echo 0 >/sys/class/gpio/gpio79/value && sleep 2 && echo 1 >/sys/class/gpio/gpio79/value

action=false
action=true
if [ "$action" = true ]; then
	logger "INFO do fresh up"
	#/sbin/reboot
fi


