<?php

namespace Sharp\Classes\Core;

abstract class AbstractEvent
{
    public function getName(): string
    {
        return get_called_class();
    }
}