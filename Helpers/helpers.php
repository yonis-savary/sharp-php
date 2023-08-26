<?php

use Sharp\Classes\Core\Logger;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Session;
use Sharp\Classes\Web\Router;

/**
 * Shortcut to `Session::getInstance()->` for your views
 * return null by default
 * @param string $key Wanted session key
 * @return mixed Key value or null
 */
function session(string $key): mixed
{
    return Session::getInstance()->get($key, null);
}

function sessionSet(string $key, mixed $value)
{
    return Session::getInstance()->set($key, $value);
}





function cache(string $key, mixed $default=false)
{
    return Cache::getInstance()->get($key, $default);
}

function cacheSet(string $key, mixed $value, int $timeToLive=3600*24)
{
    return Cache::getInstance()->set($key, $value, $timeToLive);
}






function sharpDebugMeasure(callable $callback, string $label="Measurement")
{
    $start = hrtime(1000);
    $callback();
    $delta = (hrtime(1000) - $start) / 1000;
    echo "$label : $delta µs (". $delta/1000 ."ms)\n";
}





function addRoutes(...$routes)
{
    Router::getInstance()->addRoutes(...$routes);
}

function groupRoutes(
    string|array $pathPrefixes,
    string|array $middlewares,
    callable $routeDeclaration
) {
    $router = Router::getInstance();
    $router->group([
        "path" => $pathPrefixes,
        "middlewares" => $middlewares
    ], $routeDeclaration);
}





function buildQuery(string $query, array $context=[])
{
    return Database::getInstance()->build($query, $context);
}

function query(string $query, array $context=[])
{
    return Database::getInstance()->query($query, $context);
}




function debug    (mixed ...$messages) { Logger::getInstance()->debug(...$messages); }
function info     (mixed ...$messages) { Logger::getInstance()->info(...$messages); }
function notice   (mixed ...$messages) { Logger::getInstance()->notice(...$messages); }
function warning  (mixed ...$messages) { Logger::getInstance()->warning(...$messages); }
function error    (mixed ...$messages) { Logger::getInstance()->error(...$messages); }
function critical (mixed ...$messages) { Logger::getInstance()->critical(...$messages); }
function alert    (mixed ...$messages) { Logger::getInstance()->alert(...$messages); }
function emergency(mixed ...$messages) { Logger::getInstance()->emergency(...$messages); }