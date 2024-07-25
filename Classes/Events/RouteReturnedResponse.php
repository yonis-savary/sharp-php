<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;

/**
 * This event is triggered when `Router` cannot find a route matching a request
 */
class RouteReturnedResponse extends AbstractEvent
{
    public function __construct(
        public Route &$route,
        public Response &$response,
        public mixed &$rawResponse,
        public Request &$request
    ){}
}