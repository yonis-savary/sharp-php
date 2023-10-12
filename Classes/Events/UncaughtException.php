<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;
use Throwable;

/**
 * This event is triggered when an uncaught exception comes to php's exception handler
 */
class UncaughtException extends AbstractEvent
{
    public function __construct(
        public Throwable $exception
    ){}
}