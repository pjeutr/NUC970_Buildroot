#!/bin/sh

# REMARK regarding keys
# /root/.ssh/id_rsa is used for communication between controllers
# /etc/dropbear/dropbear_ecdsa_host_key is used for communication to github

dbclient -y -o StrictHostKeyChecking=no -i /etc/dropbear/dropbear_ecdsa_host_key $*
