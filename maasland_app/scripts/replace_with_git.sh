#!/bin/sh
#
# Replace DuoApp with the latest version from github
#
#sshfs root@maasland:/ ~/mounts/match 
#cd /Users/pjeutr/Mounts/match/maasland_app
#scripts/replace_with_git.sh

git init .
git remote add -f origin https://github.com/pjeutr/DuoApp.git 
#git remote set-head origin -a
git checkout -f main

