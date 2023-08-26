<?php

namespace Sharp\Tests\Classes;

use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\MiddlewareInterface;

class MiddlewareA implements MiddlewareInterface
{
    public static function handle(Request $request): Request|Response
    {
        return $request;
    }
}