#!/bin/sh
#
# Replace DuoApp with the latest version from github
#

# .git will be placed in current dir, /maasland_app/ has space
cd /maasland_app/
# remove old git repos info to prevent errors
rm -rf .git/

# prevents: error message "unable to get local issuer certificate"
git config --global http.sslVerify false

#
git init .
git remote add -f origin https://github.com/pjeutr/DuoApp.git 
#git remote set-head origin -a
git checkout -f main

# restart services to empty opcache
/scripts/restart_services.sh