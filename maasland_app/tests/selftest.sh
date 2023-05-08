#!/bin/bash

   if [ -e /var/run/flexess.pid ]; then
      echo inputListener is
      ps -a | grep [i]nputListener\.php && echo "Running" || echo "Not running"
      echo pid=`cat /var/run/flexess.pid`
   else
      echo inputListener is NOT running
      exit 1
   fi




