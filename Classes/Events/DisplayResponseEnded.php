<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Http\Response;

class DisplayResponseEnded extends AbstractEvent
{
    public function __construct(
        public Response $response
    ){}
}