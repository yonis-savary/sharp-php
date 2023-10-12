<?php

namespace Sharp\Classes\Events;

use Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered when a user is logged in with Authentication
 */
class AuthenticatedUser extends AbstractEvent
{
    public function __construct(
        public array $user,
        public string $model
    ){}
}