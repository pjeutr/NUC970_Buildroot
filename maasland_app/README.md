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