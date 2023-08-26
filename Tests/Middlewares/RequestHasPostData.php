<?php

namespace Sharp\Tests\Middlewares;

use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\MiddlewareInterface;

class RequestHasPostData implements MiddlewareInterface
{
    public static function handle(Request $request): Request|Response
    {
        $post = $request->post();
        return count($post) ? $request: Response::json("Response must have POST data");
    }
}