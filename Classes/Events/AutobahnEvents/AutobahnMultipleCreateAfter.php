<?php

namespace Sharp\Classes\Events\AutobahnEvents;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Data\DatabaseQuery;

/**
 * This event is triggered after inserting multiple rows with Autobahn
 */
class AutobahnMultipleCreateAfter extends AbstractEvent
{
    public function __construct(
        public string $model,
        public DatabaseQuery &$query,
        public ?array $insertedIdList
    ){}
}