<?php

use Sharp\Classes\Http\Response;
use Sharp\Classes\Core\Logger;

/**
 * Exception kill the request if not handled :
 * - For web users : a simple 'Internal Server Error' is displayed
 * - For CLI users : a message is displayed telling that an error occured
 */
set_exception_handler(function(Throwable $exception){
    Logger::getInstance()->logThrowable($exception);

    if (php_sapi_name() === "cli")
        die(join("\n", [
            "\n",
            "-----------------------------------------------",
            " Got an exception/error, please read your logs ",
            "-----------------------------------------------\n"
        ]));

    $res = new Response("Internal Server Error", 500, ["Content-Type" => "text/plain"]);
    $res->display();
    die;
});

/**
 * To use the same code a the exception handler,
 * we transform the error into an `ErrorException`
 */
set_error_handler(function(int $code, string $message, string $file, int $line){
    throw new ErrorException($message, $code, 1, $file, $line);
});