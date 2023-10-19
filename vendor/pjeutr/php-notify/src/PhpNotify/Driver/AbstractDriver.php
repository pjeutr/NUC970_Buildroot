<?php

namespace Pjeutr\PhpNotify\Driver;

use Pjeutr\PhpNotify\Observer;

abstract class AbstractDriver {

    /**
     * @var Observer $observer
     */
    protected $observer;

    public function __construct(Observer $observer) {
        $this->observer = $observer;
    }
}