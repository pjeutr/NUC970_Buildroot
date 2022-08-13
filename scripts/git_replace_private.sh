#!/bin/sh
#
# Replace DuoApp with the latest version from github
#

# Dropbear can not do git through ssh, let's redirect through dbclient
# https://groups.google.com/g/beagleboard/c/h6XiKjT9-ZI?pli=1
export GIT_SSH=/maasland_app/scripts/gitssh.sh 

# .git will be placed in current dir, /maasland_app/ has space
cd /maasland_app/
# remove old git repos info to prevent errors
rm -rf .git/

# prevents: error message "unable to get local issuer certificate"
git config --global http.sslVerify false

#
git init .
git remote add -f origin git@github.com:pjeutr/DuoApp.git
#git remote set-head origin -a
git checkout -f main

# restart services to empty opcache
/scripts/restart_services.sh
