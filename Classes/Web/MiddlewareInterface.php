<?php

namespace Sharp\Classes\Web;

use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;

interface MiddlewareInterface
{
    public static function handle(Request $request) : Request|Response;
}

