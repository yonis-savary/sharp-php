<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;

final class CustomEvent extends AbstractEvent
{
    protected ?string $name = null;
    public array $extra = [];

    public function __construct(string $name, array $extra=[])
    {
        $this->name = $name;
        $this->extra = $extra;
    }

    public function getName(): string
    {
        return $this->name;
    }
}