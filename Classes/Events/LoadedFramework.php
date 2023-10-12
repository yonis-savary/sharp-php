<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered after the framework and applications are loaded
 */
class LoadedFramework extends AbstractEvent
{
    public function __construct()
    {}
}