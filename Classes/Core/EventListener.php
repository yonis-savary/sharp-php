<?php

namespace Sharp\Classes\Core;

use Sharp\Classes\Core\Component;
use Sharp\Classes\Events\DispatchedEvent;
use Sharp\Classes\Data\ObjectArray;

class EventListener
{
    use Component;

    protected array $handlers = [];
    protected array $registeredEvents = [];

    /**
     * Attach callback(s) to an event
     * When the given event is triggered, all given callbacks are called
     */
    public function on(string $event, callable ...$callbacks): void
    {
        $this->handlers[$event] ??= [];
        array_push($this->handlers[$event], ...$callbacks);

        $this->registeredEvents[] = $event;
    }

    /**
     * Trigger an event and call every attached callbacks (if any)
     *
     * @param AbstractEvent $event Event object to trigger
     * @param mixed ...$args Parameters to give to the event's callbacks
     */
    public function dispatch(AbstractEvent $event): void
    {
        $eventName = $event->getName();

        $results = [];
        if (in_array($eventName, $this->registeredEvents))
        {
            $results = ObjectArray::fromArray($this->handlers[$eventName] ?? [])
            ->map(fn($handler) => $handler($event))
            ->collect();
        }


        if ($eventName !== DispatchedEvent::class)
            $this->dispatch(new DispatchedEvent($event, $results));
    }
}