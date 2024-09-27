<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Data\Database;

class DatabaseBuildAfter extends AbstractEvent
{
    public function __construct(
        public readonly string $query,
        public readonly array $context,
        public readonly string $builtQuery,
        public readonly Database $database
    ){}
}