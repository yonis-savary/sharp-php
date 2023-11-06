<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Extras\QueueHandler;

class QueueHandlerTest extends TestCase
{
    public function test_processQueueItem()
    {

        $handler = new class {
            use QueueHandler;

            static int $acc = 0;
            static ?int $lastProcessed = null;

            public static function addNumber(int $number)
            {
                self::pushQueueItem(["number" => $number]);
            }

            protected static function processQueueItem(array $data)
            {
                $n = $data["number"];
                self::$lastProcessed = $n;
                self::$acc += $n;
            }
        };


        for ($i=1; $i<=30; $i++)
            $handler::addNumber($i);


        $sumOfN = fn($n) => ($n**2 + $n) / 2;

        $handler::processQueue();
        $this->assertEquals(10, $handler::$lastProcessed);
        $this->assertEquals($sumOfN(10) , $handler::$acc);

        $handler::processQueue();
        $this->assertEquals(20, $handler::$lastProcessed);
        $this->assertEquals($sumOfN(20) , $handler::$acc);

        $handler::processQueue();
        $this->assertEquals(30, $handler::$lastProcessed);
        $this->assertEquals($sumOfN(30) , $handler::$acc);

    }
}