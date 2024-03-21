<?php

use Sharp\Classes\Core\Logger;

/**
 * Shortcut to `Logger::getInstance()->debug()`
 * @param mixed ...$messages Informations/Data to log
 */
function debug(mixed ...$messages)
{
    Logger::getInstance()->debug(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->info()`
 * @param mixed ...$messages Informations/Data to log
 */
function info(mixed ...$messages)
{
    Logger::getInstance()->info(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->notice()`
 * @param mixed ...$messages Informations/Data to log
 */
function notice(mixed ...$messages)
{
    Logger::getInstance()->notice(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->warning()`
 * @param mixed ...$messages Informations/Data to log
 */
function warning(mixed ...$messages)
{
    Logger::getInstance()->warning(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->error()`
 * @param mixed ...$messages Informations/Data to log
 */
function error(mixed ...$messages)
{
    Logger::getInstance()->error(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->critical()`
 * @param mixed ...$messages Informations/Data to log
 */
function critical(mixed ...$messages)
{
    Logger::getInstance()->critical(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->alert()`
 * @param mixed ...$messages Informations/Data to log
 */
function alert(mixed ...$messages)
{
    Logger::getInstance()->alert(...$messages);
}

/**
 * Shortcut to `Logger::getInstance()->emergency()`
 * @param mixed ...$messages Informations/Data to log
 */
function emergency(mixed ...$messages)
{
    Logger::getInstance()->emergency(...$messages);
}
