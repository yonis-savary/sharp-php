<?php

/**
 * Sharp helpers functions
 * --------------------------------------------------
 * This file contains some global functions that were written to
 * make developpement faster, as a lot of component need the call of `getInstance()`,
 * it is easier to create an alias for common actions
 */

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