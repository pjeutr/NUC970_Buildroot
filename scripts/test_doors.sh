#!/bin/bash

open_door()
{
    #curl -d "_method=POST&user[name]=Alex$i&user[keycode]=Alex2&user[group_id]=1" -X POST http://192.168.178.137/?/users
    wget -q -O - "http://192.168.178.137/?/door/$1" 
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
	

