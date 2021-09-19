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

For now we used composer require ekongyun/php-coap (https://github.com/cfullelove/PhpCoap)
Which pulls in react/react: 0.4.*
https://github.com/reactphp/reactphp/tree/v0.4.1


composer req ekongyun/php-coap -> pjeutr/php-coap, remove react requirement which pulls in to much bloat
composer require calcinai/rubberneck -> react/event-loop v0.4.3 (only react thing we need)
#composer req devgiants/filesystem-gpio (not usable)

