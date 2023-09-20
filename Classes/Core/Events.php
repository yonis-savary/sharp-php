<?php

namespace Sharp\Classes\Core;

use Sharp\Classes\Core\Component;

class Events
{
    use Component;

    const SELF_EVENT = "dispatchedEvent";

    protected array $handlers = [];

    /**
     * Attach callback(s) to an event
     * When the given event is triggered, all given callbacks are called
     */
    public function on(string $event, callable ...$callbacks): void
    {
        $this->handlers[$event] ??= [];
        array_push($this->handlers[$event], ...$callbacks);
    }

    /**
     * Trigger an event and call every attached callbacks (if any)
     *
     * @param string $event Event name to trigger
     * @param mixed ...$args Parameters to give to the event's callbacks
     */
    public function dispatch(string $event, mixed ...$args): void
    {
        $results = array_map(
            fn($handler) => $handler(...$args),
            $this->handlers[$event] ?? []
        );

        if ($event === self::SELF_EVENT)
            return;

        $this->dispatch(self::SELF_EVENT, [
            "event" => $event,
            "args" => $args,
            "results" => $results
        ]);
    }
}