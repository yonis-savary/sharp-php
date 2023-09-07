<?php

namespace Sharp\Classes\Core;

use Sharp\Classes\Core\Component;

class Events
{
    use Component;

    protected array $handlers = [];

    /**
     * Attach one callback or more to an event
     * When the event is triggered, all given callbacks are called
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
        $results = [];
        foreach ($this->handlers[$event] ?? [] as $handler)
            $results[] = $handler(...$args);

        $selfEvent = "dispatchedEvent";
        if ($event != $selfEvent)
            $this->dispatch($selfEvent, [
                "event" => $event,
                "args" => $args,
                "results" => $results
            ]);
    }
}