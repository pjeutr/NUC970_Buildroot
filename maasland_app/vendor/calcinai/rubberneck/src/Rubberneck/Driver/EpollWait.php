<?php

/**
 * @package    calcinai/phpi
 * @author     Michael Calcinai <michael@calcin.ai>
 */

namespace Calcinai\Rubberneck\Driver;

use Calcinai\Rubberneck\Observer;

class EpollWait extends AbstractDriver implements DriverInterface {


    private $cli_command = '/root/epoll_userspace';

    public function watch($path) {
        // dev/wiegand is where the epoll is attached to
        //$subprocess_cmd = sprintf($cli_command.' %s 2>/dev/null', '/dev/wiegand');
        $subprocess_cmd = sprintf('/root/epoll_userspace %s 2>/dev/null', '/dev/wiegand');

        $this->observer->getLoop()->addReadStream(popen($subprocess_cmd, 'r'), [$this, 'onData']);

        return true;
    }


    /**
     * Public vis for callback, not cause it should be called by anyone.
     *
     * @param $stream
     */
    public function onData($stream){    
        //mylog("onData\n");    
        $event_lines = fread($stream, 1024);
        mylog($event_lines."\n");  
        //TODO more checks before emitting
        //return /sys/kernel/wiegand/read this is where we can get the value

        $this->observer->emit(Observer::EVENT_MODIFY, ["/sys/kernel/wiegand/read"]);


    }

    public static function hasDependencies() {
        //return `command -v {$cli_command}` !== null;
        return true;
        //return `command -v /root/epoll_userspace` !== null;
    }
}
