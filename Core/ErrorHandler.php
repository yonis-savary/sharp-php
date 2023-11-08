<?php

use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Events\UncaughtException;

/**
 * Exception kill the request if not handled :
 * - For web users : a simple 'Internal Server Error' is displayed
 * - For CLI users : a message is displayed telling that an error occurred
 */
set_exception_handler(function(Throwable $exception){
    try
    {
        EventListener::getInstance()->dispatch(new UncaughtException($exception));
        Logger::getInstance()->error($exception);

        if (php_sapi_name() === "cli")
            die(join("\n", [
                "\n",
                "_____________________________________________",
                "Got an exception/error, please read your logs",
                "(".$exception->getMessage()." at ".$exception->getFile().":".$exception->getLine().")"
            ]));

        (new Response("Internal Server Error", 500, ["Content-Type" => "text/plain"]))
        ->display();
        die;
    }
    catch (Throwable $err)
    {
        // In case everything went wrong even logging/events !

        http_response_code(500);
        echo "Internal Server Error <hr>";
        echo $err->getMessage();
        die;
    }
});

/**
 * To use the same code a the exception handler,
 * we transform the error into an `ErrorException`
 */
set_error_handler(function(int $code, string $message, string $file, int $line){
    throw new ErrorException($message, $code, 1, $file, $line);
});