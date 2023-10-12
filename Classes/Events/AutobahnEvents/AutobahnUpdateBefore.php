<?php

namespace Sharp\Classes\Events\AutobahnEvents;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Data\DatabaseQuery;

/**
 * This event is triggered before updating row(s) with Autobahn
 */
class AutobahnUpdateBefore extends AbstractEvent
{
    public function __construct(
        public string $model,
        public mixed $primaryKeyValue,
        public DatabaseQuery &$query
    ){}
}