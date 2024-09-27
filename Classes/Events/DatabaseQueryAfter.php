<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Data\Database;

class DatabaseQueryAfter extends AbstractEvent
{
    public function __construct(
        public readonly string $query,
        public readonly array $results,
        public readonly Database $database
    ){}
}