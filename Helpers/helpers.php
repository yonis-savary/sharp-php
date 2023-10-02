<?php

/**
 * Sharp helpers functions
 * --------------------------------------------------
 * This file contains some global functions that were written to
 * make developpement faster, as a lot of component need the call of `getInstance()`,
 * it is easier to create an alias for common actions
 */

use Sharp\Classes\Core\Events;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Configuration;
use Sharp\Classes\Env\Session;

/**
 * Shortcut to `Session::getInstance()->get()`
 *
 * @param string $key Wanted session key
 * @return mixed Key value or null if the key isn't set
 */
function session(string $key): mixed
{
    return Session::getInstance()->get($key, null);
}

/**
 * Shortcut to `Session::getInstance()->set()`
 *
 * @param string $key Key to set
 * @param mixed $value Key's value
 */
function sessionSet(string $key, mixed $value): void
{
    Session::getInstance()->set($key, $value);
}

/**
 * Get a value from the global configuration instance
 */
function config(string $key, mixed $default=null): mixed
{
    return Configuration::getInstance()->get($key, $default);
}

/**
 * Shortcut to `Cache::getInstance()->get()`
 *
 * @param string $key Wanted session key
 * @return mixed Key value or null if the key isn't set
 */
function cache(string $key, mixed $default=false): mixed
{
    return Cache::getInstance()->get($key, $default);
}

/**
 * Shortcut to `Cache::getInstance()->set()`
 *
 * @param string $key Key to set
 * @param mixed $value Key's value
 */
function cacheSet(string $key, mixed $value, int $timeToLive=3600*24): void
{
    Cache::getInstance()->set($key, $value, $timeToLive);
}

/**
 * Debug function: used to measure an execution time
 *
 * @param callable $callback Function to measure (execution time)
 * @param string $label You can give the measurement a name
 * @return mixed Return the callback return value
 */
function sharpDebugMeasure(callable $callback, string $label="Measurement"): mixed
{
    $start = hrtime(1000);
    $returnValue = $callback();
    $delta = (hrtime(1000) - $start) / 1000;

    $infoString = "$label : $delta µs (". $delta/1000 ."ms)";

    debug($infoString);

    return $returnValue;
}

/**
 * Shortcut to `Database::getInstance()->build()`
 *
 * @param string $query SQL Query with placeholders (`{}`)
 * @param array $context Ordered array, given values will replace query's placeholders
 * @return string Built query
 */
function buildQuery(string $query, array $context=[]): string
{
    return Database::getInstance()->build($query, $context);
}

/**
 * Shortcut to `Database::getInstance()->query()`
 *
 * Execute a query and return the result
 *
 * @param string $query SQL Query with placeholders (`{}`)
 * @param array $context Ordered array, given values will replace query's placeholders
 * @return array Query result rows (raw, associative array)
 */
function query(string $query, array $context=[]): array
{
    return Database::getInstance()->query($query, $context);
}


function lastInsertId(): int|false
{
    return Database::getInstance()->lastInsertId();
}

/**
 * Attach callbacks to a given events (`Events::getInstance()` is used)
 *
 * @param string $event Target event name
 * @param callable ...$callbacks Callbacks to call when $event is triggered
 */
function onEvent(string $event, callable ...$callbacks): void
{
    $events = Events::getInstance();

    foreach ($callbacks as $callback)
        $events->on($event, $callback);
}

/**
 * Trigger an event with `Event::getInstance()->dispatch`
 *
 * @param string $event Event name to trigger
 * @param mixed ...$args Arguments to give to the event's callbacks
 */
function dispatch(string $event, mixed ...$args): void
{
    Events::getInstance()->dispatch($event, ...$args);
}