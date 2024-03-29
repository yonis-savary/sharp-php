<?php

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Core\EventListener;

/**
 * Attach callbacks to a given events (`Events::getInstance()` is used)
 *
 * @param string $event Target event name
 * @param callable ...$callbacks Callbacks to call when $event is triggered
 */
function onEvent(string $event, callable ...$callbacks): void
{
    $events = EventListener::getInstance();

    foreach ($callbacks as $callback)
        $events->on($event, $callback);
}

/**
 * Trigger an event with `Event::getInstance()->dispatch`
 *
 * @param AbstractEvent $event Event name to trigger
 * @param mixed ...$args Arguments to give to the event's callbacks
 */
function dispatch(AbstractEvent $event, mixed ...$args): void
{
    EventListener::getInstance()->dispatch($event, ...$args);
}