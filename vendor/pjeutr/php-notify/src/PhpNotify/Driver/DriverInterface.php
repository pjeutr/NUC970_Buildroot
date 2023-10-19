<?php

namespace Pjeutr\PhpNotify\Driver;

use Pjeutr\PhpNotify\Observer;

interface DriverInterface {
    public function watch($path);
    public static function hasDependencies();
}