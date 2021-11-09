#!/bin/sh
#
# 

#vendor/bin/cigar -c tests/api1.json --url=http://192.168.178.137

#api1 is theory
#api2 is for some reason this passes the tests, lijkt dat status nooit de goeie state meegeeft
#vendor/bin/cigar -c tests/api2.json --url=http://192.168.178.165
vendor/bin/cigar -c tests/api1.json --url=http://127.0.0.1
#vendor/bin/cigar -c tests/api2.json



#curl http://192.168.178.165/?/api/status/2 && curl http://192.168.178.165/?/api/status/2
#curl http://192.168.178.165/?/api/output/2/0

#curl http://127.0.0.1/?/api/status/2 && curl http://127.0.0.1/?/api/status/2
#curl http://127.0.0.1/?/api/output/2/0
