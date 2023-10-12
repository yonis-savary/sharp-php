<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered when Autoloader try to load a class but the target file isn't found
 */
class FailedAutoload extends AbstractEvent
{
    public function __construct(
        public string $class,
        public string $attemptedFile
    ) {}
}