#!/bin/bash

master=$(ifconfig eth0 | awk '/inet addr/{print substr($2,6)}')
echo "-- MASTER $master --"
# master=$(avahi-browse -tpr _master._sub._maasland._udp | awk -F';' '$1 - /^=/ {print $8}')
# call script, source runs it in the same process
source /maasland_app/tests/stat.sh $1

echo
echo "-- NTP TIMESERVICE --"
chronyc tracking
#chronyc sources
chronyc sourcestats
echo
echo System time:$(date)

#php -r 'print_r(get_defined_constants());'
php -f '/maasland_app/tests/analysis.php'

echo
echo
echo "-- SLAVES --"
slaves=$(avahi-browse -tpr _maasland._udp | awk -F';' '$1 - /^=/ {print $8}')
#echo $slaves
for url in $slaves
do
	#echo $url == $master
	if [[ $url == $master ]]; then
		#do nothing when master
		echo master
		continue
	fi

	#query slaves for statistical data
	response=$(curl -f -s http://$url/?/tests/stat.sh/$1)
	# echo debug="${response:0:120}"

	echo "--- SLAVE $url ---- $(curl http://$url/?/api/overview)"
	if [[ "$response" =~ ^tests:.* ]]; then
		#remove pre tags
		sed 's/<[^>]*>//g' <<< "$response"
	else 
		echo "no valid response"
	fi
done
echo "THE END"