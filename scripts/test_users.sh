#!/bin/bash

create_user()
{
    #wget "http://192.168.178.137/?/users" --post-data "_method=POST&user[name]=dummy&user[keycode]=1000&user[group_id]=2"
    #curl -d "_method=POST&user[name]=Alex$i&user[keycode]=Alex2&user[group_id]=1" -X POST http://192.168.178.137/?/users
    wget "http://192.168.178.137/?/users" --post-data "_method=POST&user[name]=dummy$i&user[keycode]=1000$i&user[group_id]=2" 
    printf "user $1  created\n"
}

#create 1000 users 
echo "Create users ${BASH_VERSION}..."
for i in {1..1000..1}
do create_user $i
done
	

