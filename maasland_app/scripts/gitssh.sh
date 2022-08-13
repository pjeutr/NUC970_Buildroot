#!/bin/sh
dbclient -y -i -o StrictHostKeyChecking=no /etc/dropbear/dropbear_ecdsa_host_key $*
