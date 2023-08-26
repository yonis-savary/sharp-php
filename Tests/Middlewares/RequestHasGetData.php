<?php

namespace Sharp\Tests\Middlewares;

use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\MiddlewareInterface;

class RequestHasGetData implements MiddlewareInterface
{
    public static function handle(Request $request): Request|Response
    {
        $get = $request->get();
        return count($get) ? $request: Response::json("Response must have GET data");
    }
}