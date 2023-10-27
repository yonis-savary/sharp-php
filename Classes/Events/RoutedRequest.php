<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Web\Route;

/**
 * This event is triggered when a Router is about to call a route's callback
 */
class RoutedRequest extends AbstractEvent
{
    public function __construct(
        public Request $request,
        public Route $route
    ) {}
}