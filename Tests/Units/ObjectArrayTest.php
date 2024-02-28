<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Data\ObjectArray;

class ObjectArrayTest extends TestCase
{
    public function test_fromArray()
    {
        $this->assertInstanceOf(ObjectArray::class, ObjectArray::fromArray([1,2,3]));

        $this->assertEquals([1,2,3], ObjectArray::fromArray([1,2,3])->collect());
        $this->assertEquals(["A","B","C"], ObjectArray::fromArray(["A","B","C"])->collect());
    }

    public function test_fromExplode()
    {
        $this->assertInstanceOf(ObjectArray::class, ObjectArray::fromExplode(",", "1,2,3"));

        $this->assertEquals(["1","2","3"], ObjectArray::fromExplode(",", "1,2,3")->collect());
    }

    public function test_fromQuery()
    {
        $this->assertEquals(
            ['Alfred', 'Francis', 'Martin', 'Quentin', 'Steven'],
            ObjectArray::fromQuery("SELECT name, birth_year FROM test_sample_data")->collect()
        );

        $this->assertEquals(
            [1899, 1939, 1942, 1963, 1946],
            ObjectArray::fromQuery("SELECT birth_year, name FROM test_sample_data")->collect()
        );
    }

    public function test_push()
    {
        $arr = new ObjectArray();
        $arr = $arr->push("A");
        $arr = $arr->push("B", "C");

        $this->assertEquals(["A", "B", "C"], $arr->collect());
    }

    public function test_pop()
    {
        $arr = new ObjectArray([1,2,3]);

        $this->assertEquals([1,2,3], $arr->collect());
        $arr = $arr->pop();
        $this->assertEquals([1,2], $arr->collect());
        $arr = $arr->pop();
        $this->assertEquals([1], $arr->collect());
        $arr = $arr->pop();
        $this->assertEquals([], $arr->collect());
    }

    public function test_shift()
    {
        $arr = new ObjectArray([1,2,3]);

        $this->assertEquals([1,2,3], $arr->collect());
        $arr = $arr->shift();
        $this->assertEquals([2,3], $arr->collect());
        $arr = $arr->shift();
        $this->assertEquals([3], $arr->collect());
        $arr = $arr->shift();
        $this->assertEquals([], $arr->collect());
    }

    public function test_unshift()
    {
        $arr = new ObjectArray();

        $arr = $arr->unshift(3);
        $this->assertEquals([3], $arr->collect());
        $arr = $arr->unshift(2);
        $this->assertEquals([2,3], $arr->collect());
        $arr = $arr->unshift(1);
        $this->assertEquals([1,2,3], $arr->collect());
    }

    public function test_forEach()
    {
        $arr = new ObjectArray([1,2,3,4,5]);
        $acc = 0;

        $arr->foreach(function($n) use (&$acc) { $acc += $n; });
        $this->assertEquals(5+4+3+2+1, $acc);
    }

    public function test_map()
    {
        $arr = new ObjectArray([1,2,3]);
        $transformed = $arr->map(fn($x) => $x*3);

        $this->assertEquals([1,2,3], $arr->collect());
        $this->assertEquals([3,6,9], $transformed->collect());
    }

    public function test_filter()
    {
        $isEven = fn($x) => $x % 2 === 0;

        $arr = new ObjectArray([0,1,2,3,4,5,6,7,8,9]);
        $copy = $arr->filter($isEven);

        $this->assertEquals([0,1,2,3,4,5,6,7,8,9], $arr->collect());
        $this->assertEquals([0,2,4,6,8], $copy->collect());

        $arr = new ObjectArray(["A", "", null, "B", 0, false, "C"]);
        $arr = $arr->filter();
        $this->assertEquals(["A", "B", "C"], $arr->collect());
    }


    public function test_sortByKey()
    {
        $names = ObjectArray::fromArray([
            ["name" => "Malcolm", "age" => 18],
            ["name" => "Melody", "age" => 40],
            ["name" => "Holly", "age" => 35],
            ["name" => "Sylvester", "age" => 80],
            ["name" => "Clyde", "age" => 35],
            ["name" => "Eliot", "age" => 36],
            ["name" => "Peace", "age" => 19],
            ["name" => "Mortimer", "age" => 50],
        ]);

        $sorted = $names->sortByKey(fn($person) => $person["age"])->collect();
        $reversed = $names->sortByKey(fn($person) => $person["age"], true)->collect();

        $this->assertEquals("Sylvester", $sorted[7]["name"]);
        $this->assertEquals("Malcolm", $sorted[0]["name"]);

        $this->assertEquals("Sylvester", $reversed[0]["name"]);
        $this->assertEquals("Malcolm", $reversed[7]["name"]);
    }

    public function test_unique()
    {
        $arr = new ObjectArray([0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9]);
        $copy = $arr->unique();

        $this->assertEquals([0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9], $arr->collect());
        $this->assertEquals([0,1,2,3,4,5,6,7,8,9], $copy->collect());
    }

    public function test_diff()
    {
        $arr = new ObjectArray(["red", "green", "blue"]);
        $copy = $arr->diff(["red"]);

        $this->assertEquals(["red", "green", "blue"], $arr->collect());
        $this->assertEquals(["green", "blue"], $copy->collect());
    }

    public function test_slice()
    {
        $arr = new ObjectArray([1,2,3,4,5]);

        $this->assertEquals([3,4,5], $arr->slice(2)->collect());
        $this->assertEquals([3,4], $arr->slice(2, 2)->collect());
        $this->assertEquals([1,2,3,4,5], $arr->collect());
    }

    public function test_collect()
    {
        $arr = new ObjectArray([1,2,3]);
        $this->assertEquals([1,2,3], $arr->collect());
    }

    public function test_join()
    {
        $arr = new ObjectArray([1,2,3]);
        $this->assertEquals("1,2,3", $arr->join(","));
    }

    public function test_length()
    {
        $arr = new ObjectArray([1,2,3]);

        $this->assertEquals(3, $arr->length());
    }

    public function test_find()
    {
        $persons = [
            ["name" => "Vincent", "age" => 18],
            ["name" => "Damon",   "age" => 15],
            ["name" => "Hollie",  "age" => 23],
            ["name" => "Percy",   "age" => 14],
            ["name" => "Yvonne",  "age" => 35],
            ["name" => "Jack",    "age" => 56],
        ];

        $arr = new ObjectArray($persons);

        $vincent = $arr->find(fn($x) => $x["age"] === 18);
        $this->assertEquals($persons[0], $vincent);
        $this->assertNull($arr->find(fn($x) => $x["name"] === "Hugo"));
    }

    public function test_toAssociative()
    {
        $letters = ["A", "B", "C"];

        $arr = new ObjectArray($letters);

        $results = $arr->toAssociative(fn($value) => [$value, "$value-$value"]);

        $this->assertEquals([
            "A" => "A-A",
            "B" => "B-B",
            "C" => "C-C"
        ], $results);
    }

    public function test_reverse()
    {
        $arr = new ObjectArray([1,2,3]);
        $copy = $arr->reverse();

        $this->assertEquals([1,2,3], $arr->collect());
        $this->assertEquals([3,2,1], $copy->collect());
    }

    public function test_reduce()
    {
        $myArray = new ObjectArray(range(0, 10));

        $this->assertEquals(
            30,
            $myArray->filter(fn($x) => $x%2==0)
            ->reduce(fn($acc, $cur) => $acc + $cur, 0)
        );
    }

    public function test_any()
    {
        $arr = new ObjectArray([1,2,3,4,5]);

        $this->assertTrue($arr->any(fn($x) => $x > 0));
        $this->assertFalse($arr->any(fn($x) => $x < 0));
    }

    public function test_all()
    {
        $arr = new ObjectArray([1,2,3,4,5]);

        $this->assertTrue($arr->all(fn($x) => $x > 0));
        $this->assertFalse($arr->all(fn($x) => $x < 0));
        $this->assertFalse($arr->all(fn($x) => $x === 5));
    }
}