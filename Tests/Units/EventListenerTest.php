<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\CustomEvent;
use Sharp\Classes\Core\EventListener;

class EventListenerTest extends TestCase
{
    public function test_events()
    {

        $myVar = 0;

        $handlerA = new EventListener();
        $handlerA->on("change", function() use (&$myVar) { $myVar = 1; });

        $handlerB = new EventListener();
        $handlerB->on("change", function() use (&$myVar) { $myVar = 2; });

        $handlerA->dispatch(new CustomEvent("change"));
        $this->assertEquals(1, $myVar);

        $handlerB->dispatch(new CustomEvent("change"));
        $this->assertEquals(2, $myVar);

        $handlerA->dispatch(new CustomEvent("change"));
        $this->assertEquals(1, $myVar);

        $handlerC = new EventListener();
        $handlerC->on("change", function(CustomEvent $value) use (&$myVar) {
            $myVar = $value->extra["value"];
        });

        for($i=0; $i<5; $i++)
        {
            $handlerC->dispatch(new CustomEvent("change", ["value" => $i]));
            $this->assertEquals($i, $myVar);
        }
    }
}