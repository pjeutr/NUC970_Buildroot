#!/bin/bash

echo "Content-type: text/html"
echo ""

echo '<html>'
echo '<head>'
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
echo '<title>Environment Variables</title>'
echo '</head>'
echo '<body>'
echo 'Environment Variables:'
echo '<pre>'
/usr/bin/env
echo '</pre>'

pwm_value=`echo "$QUERY_STRING" | cut -d '&' -f 1 | cut -d '=' -f 2`
echo "VALUE=$pwm_value ($QUERY_STRING)"

echo $pwm_value > /sys/class/backlight/backlight_lcd/brightness

echo '</body>'
echo '</html>'

exit 0
