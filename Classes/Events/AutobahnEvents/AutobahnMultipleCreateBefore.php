<?php

namespace Sharp\Classes\Events\AutobahnEvents;

use Sharp\Classes\Core\AbstractEvent;
use Sharp\Classes\Data\ObjectArray;

/**
 * This event is triggered before inserting multiple rows with Autobahn
 */
class AutobahnMultipleCreateBefore extends AbstractEvent
{
    public function __construct(
        public ObjectArray $dataToBeInserted
    ){}
}