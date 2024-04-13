# DuoApp

Limonade was chosen, because it's small, simple and has no need for composer 
https://limonade-php.github.io/
https://github.com/sofadesign/limonade/
CRUD example from
https://github.com/apankov/library.dev/

Bootstrap v4 theme used
https://www.creative-tim.com/product/light-bootstrap-dashboard
https://github.com/creativetimofficial/light-bootstrap-dashboard

https://demos.creative-tim.com/light-bootstrap-dashboard/examples/notifications.html
https://github.com/mouse0270/bootstrap-notify

Coap - coap-listener.php
Would be nice if it can be a native server, would save space an performance
like https://github.com/obgm/libcoap-minimal/blob/main/server.cc

For now we used composer require pjeutr/php-coap (https://github.com/pjeutr/php-coap)
Which pulls in react/reactphp: 1.1.*
https://github.com/reactphp/reactphp/tree/v1.1.0

composer require 
calcinai/rubberneck -> pjeutr/php-notify (restructuring + adding EpollWait)
arrilot/dotenv-php -> pjeutr/dotenv-php (allow optional use / no .env.pho in production)

v1.8.7 - Remove changeDoorState,fix led signal on reader
v1.8.6 - Holidays + countdown icon
v1.8.5 - Groups and Doors collapsable, factory reset fix, network DHCP/MasterIP settings
v1.8.3 - memory fix
v1.8.2 - Activate readerled with a schedule and dashboard door open, fix for big reports exports
v1.8.1 - New menu structure and inlog for user/admin/super + Adjustable clean reports interval 
v1.8.0 - Autonomous slaves, through replicate button 
v1.7.6 - Autorestart at night if low memory 
v1.7.5 - fix group timezone for overnight, 
v1.7.4 - add time to settings and hwclock, so time can work without internet, add meminfo for php for debugging   
v1.7.3 - fix schedule so till can be the next day. Adjust oom score
v1.7.1 - add keycode to csv export, fix slave failing after master was offline 
v1.7.0 - fix start/end timezone bug for a group
v1.6.9 - move schedule to listener, to free up memory and prevent 
v1.6.0 - callback fix which made flexess_cron hang + refactor to static loop
v1.5.5 - fix slave bug (remove req->close) + signal beep an slave when master not found during tag scan
v1.5.5 - apiCall refactor + flexess_cron lock
v1.5.4 - vacuum db after delete + increase import upload + timezone bug in scheduled + door shortcuts on dashboard 
v1.5.3 - Add remove timezone button
v1.5.2 - Fix scheduled excessive logging
v1.5.1 - Fix scheduled excessive switching
v1.5.0 - Attendence accounting
v1.4.1 - New SD flasher
v1.2.2 - Add blinking led, to signal if network or master is missing
v1.2 - make build to use flahs from SD card
v1.1.8 - make firmwareupdate available at slave
v1.1.6 - fix blocking master and add gitssh
v1.1.4 - fix reports initial sort, add someone | doorname message | add git update in dev menu
v1.1.3 - final initial settings
v1.1.2 - fix start/end time bug + lowlevel cleanup + ipv6
v1.1.0
- added datetimepicker to timezones and adjust weekdays in overview
- reset visits @userdetail
- add "Disable access" @userdetail
- more language changes
v1.0.3 - report line adjustment + language changes
v1.0.2 
- !!!Slave controller overview with status
- Schedule update every minute
- Controller search shows only unkown controllers
- Reportlines changed to doorName@controllerName reader x
- Adding Refresh button 
- Delete reports older than 30days
- Status indicatie op dashboard of controllers online zijn.
- “Ja” en “Annuleren” bij verwijderen van gebruikers etc
v0.7.6 - fix slave alarm, add dashboard open/close and alarm on/off, debounce buttons
v0.7.5 - fix gpio resolver, enforce unique controller IP
v0.7.4 - fix getValue and .env include software version in status/gvar
v0.7.3 - fix alarm, demo db
v0.7.2 - back to ubifs, fix alarm
v0.7.1 - squashfs
v0.7.0 - translation added 
v0.6.1 - back to coap
v0.6.0 - support match4 hardware
v0.5.1 - use http ie coap
v0.5.0 - support network slave controllers