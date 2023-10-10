<?php

namespace Sharp\Tests\Units;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Autoloader;
use Sharp\Core\Utils;
use Sharp\Tests\Classes\A;
use Sharp\Tests\Classes\AA;
use Sharp\Tests\Classes\AB;
use Sharp\Tests\Classes\B;
use Sharp\Tests\Classes\BA;
use Sharp\Tests\Classes\Interfaces\InterfaceA;
use Sharp\Tests\Classes\Interfaces\InterfaceB;
use Sharp\Tests\Classes\Traits\TraitA;

class UtilsTest extends TestCase
{
    public function test_uses()
    {
        $this->assertTrue(Utils::uses(A::class, TraitA::class));
        $this->assertFalse(Utils::uses(B::class, TraitA::class));
    }

    public function test_implements()
    {
        $this->assertTrue( Utils::implements(AA::class, InterfaceA::class) );
        $this->assertTrue( Utils::implements(BA::class, InterfaceB::class) );
        $this->assertFalse( Utils::implements(AA::class, InterfaceB::class) );
        $this->assertFalse( Utils::implements(BA::class, InterfaceA::class) );
    }

    public function test_extends()
    {
        $this->assertTrue( Utils::extends(AA::class, A::class) );
        $this->assertTrue( Utils::extends(AB::class, A::class) );

        $this->assertTrue( Utils::extends(BA::class, B::class) );

        $this->assertFalse( Utils::extends(AA::class, B::class) );
        $this->assertFalse( Utils::extends(AB::class, B::class) );
    }

    public function test_normalizePath()
    {
        $this->assertEquals("domain/class.php", Utils::normalizePath("domain/class.php"));
        $this->assertEquals("domain/class.php", Utils::normalizePath("domain//class.php"));
        $this->assertEquals("domain/class.php", Utils::normalizePath("domain\\class.php"));
        $this->assertEquals("domain/class.php", Utils::normalizePath("domain\\\\class.php"));
    }

    public function test_joinPath()
    {
        $this->assertEquals("domain/class.php", Utils::joinPath("domain", "class.php"));
        $this->assertEquals("domain/class.php", Utils::joinPath("domain/", "class.php"));
        $this->assertEquals("domain/class.php", Utils::joinPath("domain", '\class.php'));
        $this->assertEquals("domain/class.php", Utils::joinPath("domain/", "/class.php"));
        $this->assertEquals("domain/class.php", Utils::joinPath("domain/", '\class.php'));
    }

    public function test_relativePath()
    {
        $this->assertEquals(
            Utils::joinPath(Autoloader::projectRoot(), "domain/class.php"),
            Utils::relativePath("domain/class.php")
        );
    }

    public function test_pathToNamespace()
    {
        $this->assertEquals(
            Utils::classnameToPath('Domain\Subdomain\Class'),
            Utils::relativePath("Domain/Subdomain/Class.php")
        );
    }

    public function test_classnameToPath()
    {
        $this->assertEquals(
            Utils::relativePath("Domain/Subdomain/Class.php"),
            Utils::classnameToPath('Domain\Subdomain\Class')
        );
    }

    private function arrayOfPaths(array $paths, Storage $storage)
    {
        return array_map(fn($e) => Utils::joinPath($storage->getRoot(), $e), $paths);
    }

    public function getDummyStorage()
    {
        $storage = Storage::getInstance()->getSubStorage(uniqid());
        foreach ([
            "a.txt",
            "b.txt",
            "dir/c.txt",
            "dir/d.txt",
            "dir/subdir/e.txt",
            "dir/subdir/f.txt"
        ] as $file) $storage->write($file, "text");
        return $storage;
    }

    public function test_exploreDirectory()
    {
        $storage = $this->getDummyStorage();

        $FILES = $this->arrayOfPaths([
            "a.txt",
            "b.txt",
            "dir/c.txt",
            "dir/d.txt",
            "dir/subdir/e.txt",
            "dir/subdir/f.txt"
        ], $storage);

        $DIRS = $this->arrayOfPaths([
            "dir",
            "dir/subdir"
        ], $storage);

        $ALL = $this->arrayOfPaths([
            "a.txt",
            "b.txt",
            "dir",
            "dir/c.txt",
            "dir/d.txt",
            "dir/subdir",
            "dir/subdir/e.txt",
            "dir/subdir/f.txt"
        ], $storage);

        $this->assertEquals($ALL, Utils::exploreDirectory($storage->getRoot(), Storage::NO_FILTER));
        $this->assertEquals($FILES, Utils::exploreDirectory($storage->getRoot(), Storage::ONLY_FILES));
        $this->assertEquals($DIRS, Utils::exploreDirectory($storage->getRoot(), Storage::ONLY_DIRS));
    }

    public function test_listFiles()
    {
        $storage = $this->getDummyStorage();

        $this->assertEquals($this->arrayOfPaths(["a.txt", "b.txt"], $storage), Utils::listFiles($storage->getRoot()));
        $this->assertEquals($this->arrayOfPaths(["dir/c.txt", "dir/d.txt"], $storage), Utils::listFiles($storage->path("dir")));
        $this->assertEquals($this->arrayOfPaths(["dir/subdir/e.txt", "dir/subdir/f.txt"], $storage), Utils::listFiles($storage->path("dir/subdir")));
    }

    public function test_listDirectories()
    {
        $storage = $this->getDummyStorage();

        $this->assertEquals($this->arrayOfPaths(["dir"], $storage), Utils::listDirectories($storage->getRoot()));
        $this->assertEquals($this->arrayOfPaths(["dir/subdir"], $storage), Utils::listDirectories($storage->path("dir")));
    }

    public function test_valueHasFlag()
    {
        $this->assertFalse(Utils::valueHasFlag(0b1010_1010, 0b0000_0001));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0000_0010));
        $this->assertFalse(Utils::valueHasFlag(0b1010_1010, 0b0000_0100));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0000_1000));
        $this->assertFalse(Utils::valueHasFlag(0b1010_1010, 0b0001_0000));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0010_0000));
        $this->assertFalse(Utils::valueHasFlag(0b1010_1010, 0b0100_0000));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b1000_0000));

        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0000_1010));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b1010_0000));

        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0000_0000));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b1010_1010));
    }

    public function test_isAssoc()
    {
        $this->assertTrue(Utils::isAssoc(["A" => 5]));
        $this->assertFalse(Utils::isAssoc([["A" => 5]]));
        $this->assertFalse(Utils::isAssoc([]));

        $this->assertFalse(Utils::isAssoc([1, 2, 3]));
        $this->assertFalse(Utils::isAssoc(["A", "B", "C"]));
    }

    public function test_toArray()
    {
        $this->assertEquals([5], Utils::toArray(5));
        $this->assertEquals([5], Utils::toArray([5]));
        $this->assertEquals([["A" => 5]], Utils::toArray(["A"=>5]));
        $this->assertEquals([["A" => 5]], Utils::toArray([["A"=>5]]));
    }
}