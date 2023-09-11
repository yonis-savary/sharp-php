<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\Component;

class ComponentTest extends TestCase
{
    const DEFAULT = 0;
    const STATE_A = 5;
    const STATE_B = 10;

    /**
     * @return \Sharp\Classes\Core\Component
     */
    protected function getDummyComponent()
    {
        return new class {
            use Component;

            public static function getDefaultInstance()
            {
                return new self(ComponentTest::DEFAULT);
            }

            public function __construct(
                public ?int $heldNumber=null
            ){}

            public function setNumber(int $n) { $this->heldNumber = $n; }
            public function getNumber(): int { return $this->heldNumber; }
        };
    }

    public function test_getDefaultInstance()
    {
        $class = $this->getDummyComponent();

        $instance = $class::getDefaultInstance();
        $this->assertEquals(self::DEFAULT, $instance->getNumber());
    }

    public function test_getInstance()
    {
        $class = $this->getDummyComponent();

        $instance = $class::getInstance();
        $instance->setNumber(self::STATE_A);

        $instance = $class::getInstance();
        $this->assertEquals(self::STATE_A, $instance->getNumber());
    }

    public function test_setInstance()
    {
        $class = $this->getDummyComponent();

        $instance = $class::getInstance();
        $instance->setNumber(self::STATE_A);

        $newInstance = new $class(self::STATE_B);
        $class::setInstance($newInstance);

        $instance = $class::getInstance();
        $this->assertEquals(self::STATE_B, $instance->getNumber());
    }

    public function test_removeInstance()
    {
        $class = $this->getDummyComponent();

        $instance = $class::getInstance();
        $instance->setNumber(self::STATE_A);

        $class::removeInstance();

        $instance = $class::getInstance();
        $this->assertEquals(self::DEFAULT, $instance->getNumber());
    }
}