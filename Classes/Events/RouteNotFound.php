<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;

/**
 * This event is triggered when `Router` cannot find a route matching a request
 */
class RouteNotFound extends AbstractEvent
{
    public function __construct(
        public Request &$request,
        public Response &$response
    ){}
}