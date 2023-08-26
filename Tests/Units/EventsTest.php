<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\Events;

class EventsTest extends TestCase
{
    public function test_events()
    {

        $myVar = 0;

        $handlerA = new Events();
        $handlerA->on("change", function() use (&$myVar) { $myVar = 1; });

        $handlerB = new Events();
        $handlerB->on("change", function() use (&$myVar) { $myVar = 2; });

        $handlerA->dispatch("change");
        $this->assertEquals(1, $myVar);

        $handlerB->dispatch("change");
        $this->assertEquals(2, $myVar);

        $handlerA->dispatch("change");
        $this->assertEquals(1, $myVar);


        $handlerC = new Events();
        $handlerC->on("change", function($value) use (&$myVar) { $myVar = $value; });

        for($i=0; $i<5; $i++)
        {
            $handlerC->dispatch("change", $i);
            $this->assertEquals($i, $myVar);
        }
    }
}