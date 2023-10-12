<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered before a view is required when rendering
 */
class BeforeViewRender extends AbstractEvent
{
    public function __construct(
        public string $view
    ){}
}