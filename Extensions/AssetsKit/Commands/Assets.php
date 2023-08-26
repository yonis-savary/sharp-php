<?php

namespace Sharp\Extensions\AssetsKit\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;

class Assets extends Command
{
    public function __invoke(Args $args)
    {
        echo "Hello";
    }
}