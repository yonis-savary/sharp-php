<?php

namespace Sharp\Classes\Core;

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