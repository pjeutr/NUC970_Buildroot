<?php

/**
 * @package    calcinai/phpi
 * @author     Michael Calcinai <michael@calcin.ai>
 */

namespace Calcinai\Rubberneck\Driver;

use Calcinai\Rubberneck\Observer;

class EpollWait extends AbstractDriver implements DriverInterface {
    static $cli_command = '/scripts/epoll_userspace';

    public function watch($path) {
        // dev/wiegand is where the epoll is attached to
        //$subprocess_cmd = sprintf(self::$cli_command.' %s 2>/dev/null', $path);
        //removing pipe, to prevent starting inotifywait wrapped in a shell (takes 4% memory extra for each entry)
        $subprocess_cmd = sprintf(self::$cli_command.' %s', $path);

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

        //Can have multiple events per read (or not enough)
        // foreach(explode("\n", $event_lines) as $event_line){
        //     list($file, $events) = sscanf($event_line, '%s %s');
        //     mylog("eventLine=".$event_line);
        //     $this->observer->emit(Observer::EVENT_MODIFY, [$file]);
        // }
    }

    public static function hasDependencies() {
        $cmd = self::$cli_command;
        return `command -v {$cmd}` !== null;
        //return true;
        //return `command -v /scripts/epoll_userspace` !== null;
    }
}
