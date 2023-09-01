<?php

namespace Sharp\Classes\Core;

use Sharp\Classes\Core\Component;

class Events
{
    use Component;

    protected array $handlers = [];

    public function on(string $event, callable $callback): void
    {
        $this->handlers[$event] ??= [];
        $this->handlers[$event][] = $callback;
    }

    public function dispatch(string $event, ...$args): void
    {
        foreach ($this->handlers[$event] ?? [] as $handler)
            $handler(...$args);
    }
}