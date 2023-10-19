#!/bin/sh
dbclient -y -o StrictHostKeyChecking=no -i /etc/dropbear/dropbear_ecdsa_host_key $*
