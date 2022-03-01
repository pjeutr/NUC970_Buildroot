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

v0.7.4 - fix getValue and .env include software version in status/gvar
v0.7.3 - fix alarm, demo db
v0.7.2 - back to ubifs, fix alarm
v0.7.1 - squashfs
v0.7.0 - translation added 
v0.6.1 - back to coap
v0.6.0 - support match4 hardware
v0.5.1 - use http ie coap
v0.5.0 - support network slave controllers