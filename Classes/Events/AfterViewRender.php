<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered after a view is required when rendering
 */
class AfterViewRender extends AbstractEvent
{
    public function __construct(public string $view)
    {}
}