<?php

namespace Sharp\Classes\Events\AutobahnEvents;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Data\DatabaseQuery;

/**
 * This event is triggered after creating a row with Autobahn
 */
class AutobahnCreateAfter extends AbstractEvent
{
    public function __construct(
        public string $model,
        public array $fields,
        public array &$values,
        public DatabaseQuery &$query,
        public ?int $insertedId
    ){}
}