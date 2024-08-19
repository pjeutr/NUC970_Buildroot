#!/bin/bash

#curl http://192.168.178.165/?/api/status/2
#curl http://192.168.178.165/?/api/output/2/1
#curl http://192.168.178.165/?/api/activate/2/6

open_door()
{
    #?/api/activate/{door}/{duration}/{gpios}
    curl http://127.0.0.1/?/api/activate/$1/1
    #curl http://192.168.178.87/?/api/activate/$1/2
    #wget -q -O - "http://192.168.178.137/?/door/$1" 
    printf "door $1 opened\n"
}

#open doors 36500 times, to get db reports full = 18250
echo "Open doors ${BASH_VERSION}..."
for ((i=0; i<10250; ++i)); do
#https://stackoverflow.com/questions/51897735/backgrounded-subshells-use-incrementally-more-memory
#for i in {1..10250..1} do 
     open_door 1
     open_door 2
done
	

