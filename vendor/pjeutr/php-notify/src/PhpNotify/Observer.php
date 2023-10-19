<?php

/**
 * @package    php-notify
 */

namespace Pjeutr\PhpNotify;

use Evenement\EventEmitterTrait;

use Pjeutr\PhpNotify\Driver;

class Observer {

    use EventEmitterTrait;

    const EVENT_CREATE = 'create';
    const EVENT_MODIFY = 'modify';
    const EVENT_DELETE = 'delete';

    /**
     * @var Driver\DriverInterface
     */
    private $driver;

    protected $listeners;

    /**
     * List of available drivers in order of preference
     *
     * @var Driver\DriverInterface[]
     */
    static $drivers = [
        Driver\EpollWait::class,
        Driver\InotifyWait::class,
        Driver\Filesystem::class
    ];

    /**
     * Observer constructor
     */
    public function X__construct() {
        $driver_class = self::getBestDriver();
        $this->driver = new $driver_class($this);
    }

    public function __construct($driver_nr) {
        $driver_class = self::$drivers[$driver_nr];
        if(!$driver_class::hasDependencies()) {
            die ("ERROR: system driver not found for :".$driver_class."\n");
        }
        
        $this->driver = new $driver_class($this);
    }

    public function watch($path) {
        $this->driver->watch($path);
    }


    public function getSubscribedEvents(){
        return array_keys($this->listeners);
    }

    public static function getBestDriver(){

        foreach(self::$drivers as $driver){
            if($driver::hasDependencies()){
                return $driver;
            }
        }

        //Should never happen since the file poll can always work.
        throw new \Exception('No drivers available');
    }


    public function onCreate($callback) {
        $this->on(self::EVENT_CREATE, $callback);
    }

    public function onModify($callback) {
        $this->on(self::EVENT_MODIFY, $callback);
    }

    public function onDelete($callback) {
        $this->on(self::EVENT_DELETE, $callback);
    }

}