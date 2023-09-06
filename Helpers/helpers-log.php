<?php

use Sharp\Classes\Core\Logger;

/**
 * Shortcut to `Logger::getInstance()->debug()`
 *
 * Used to log informations with a `debug` level
 *
 * @param mixed ...$messages Informations to log (Not necessary a string)
 */
function debug(mixed ...$messages)
{
    Logger::getInstance()->debug(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->info()`
 *
 * Used to log informations with a `info` level
 *
 * @param mixed ...$messages Informations to log (Not necessary a string)
 */
function info(mixed ...$messages)
{
    Logger::getInstance()->info(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->notice()`
 *
 * Used to log informations with a `notice` level
 *
 * @param mixed ...$messages Informations to log (Not necessary a string)
 */
function notice(mixed ...$messages)
{
    Logger::getInstance()->notice(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->warning()`
 *
 * Used to log informations with a `warning` level
 *
 * @param mixed ...$messages Informations to log (Not necessary a string)
 */
function warning(mixed ...$messages)
{
    Logger::getInstance()->warning(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->error()`
 *
 * Used to log informations with a `error` level
 *
 * @param mixed ...$messages Informations to log (Not necessary a string)
 */
function error(mixed ...$messages)
{
    Logger::getInstance()->error(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->critical()`
 *
 * Used to log informations with a `critical` level
 *
 * @param mixed ...$messages Informations to log (Not necessary a string)
 */
function critical(mixed ...$messages)
{
    Logger::getInstance()->critical(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->alert()`
 *
 * Used to log informations with a `alert` level
 *
 * @param mixed ...$messages Informations to log (Not necessary a string)
 */
function alert(mixed ...$messages)
{
    Logger::getInstance()->alert(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->emergency()`
 *
 * Used to log informations with a `emergency` level
 *
 * @param mixed ...$messages Informations to log (Not necessary a string)
 */
function emergency(mixed ...$messages)
{
    Logger::getInstance()->emergency(...$messages);
}
