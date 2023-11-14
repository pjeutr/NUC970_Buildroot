#!/bin/sh
#
# Script to get memory information form php
# https://github.com/BitOne/php-meminfo/

#make local dump
coap-client -mr coap://127.0.0.1/dump_x
#upload
curl -k --upload-file /tmp/dumpx.json https://free.keep.sh
#download localy
echo "curl -L https://free.keep.sh/xxx/dumpx.json > dump_x.json"
#analyze
echo "php-meminfo/analyzer/bin/analyzer summary dump_x.json"
