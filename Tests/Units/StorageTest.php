<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Core\Utils;
use Sharp\Classes\Env\Storage;

final class StorageTest extends TestCase
{
    const SAMPLE_FILES = [
        "a.txt",
        "b.txt",
        "dir/c.txt",
        "dir/d.txt",
        "dir/subdir/e.txt",
        "dir/subdir/f.txt"
    ];

    const SAMPLE_DIRECTORIES = [
        "dir",
        "dir/subdir"
    ];

    private function getSampleStorage(bool $generateTree=false): Storage
    {
        $storage = Storage::getInstance()->getSubStorage(uniqid("storage-test"));

        if ($generateTree)
        {
            foreach (self::SAMPLE_FILES as $toWrite)
                $storage->write($toWrite, "");
        }

        return $storage;
    }

    public function test_getRoot()
    {
        $id = uniqid("storage-test");
        $sample = Storage::getInstance()->getSubStorage($id);

        $this->assertEquals(
            Storage::getInstance()->path($id),
            $sample->getRoot()
        );
    }

    public function test_getSubStorage()
    {
        $this->assertInstanceOf(
            Storage::class,
            Storage::getInstance()->getSubStorage(uniqid("storage-test"))
        );
    }

    public function test_path()
    {
        $sample = $this->getSampleStorage();

        $this->assertEquals(
            Utils::joinPath($sample->getRoot(), "file.txt"),
            $sample->path("file.txt")
        );
    }

    public function test_makeDirectory()
    {
        $sample = $this->getSampleStorage();

        $sample->makeDirectory("my-directory");

        $this->assertDirectoryExists(
            $sample->path("my-directory")
        );
    }

    public function test_getStream()
    {
        $sample = $this->getSampleStorage();

        $this->assertIsResource(
            $sample->getStream("some-file.txt", "w")
        );
    }

    public function test_write()
    {
        $sample = $this->getSampleStorage();
        $sample->write("file.txt", "Hello");
        $this->assertFileExists($sample->path("file.txt"));
    }

    public function test_read()
    {
        $sample = $this->getSampleStorage();
        $sample->write("file.txt", "Hello");
        $this->assertEquals("Hello", $sample->read("file.txt"));
    }

    public function test_isFile()
    {
        $sample = $this->getSampleStorage();
        $sample->write("file.txt", "Hello");
        $this->assertTrue($sample->isFile("file.txt"));
        $this->assertFalse($sample->isFile("inexistant.txt"));
    }

    public function test_isDirectory()
    {
        $sample = $this->getSampleStorage();
        $sample->makeDirectory("messages");
        $this->assertTrue($sample->isDirectory("messages"));
        $this->assertFalse($sample->isDirectory("inexistants"));
    }

    public function test_unlink()
    {
        $sample = $this->getSampleStorage();
        $sample->write("file.txt", "Hello");
        $this->assertFileExists($sample->path("file.txt"));
        $sample->unlink("file.txt");
        $this->assertFileDoesNotExist($sample->path("file.txt"));
    }

    public function test_removeDirectory()
    {
        $sample = $this->getSampleStorage();
        $sample->makeDirectory("messages");
        $this->assertDirectoryExists($sample->path("messages"));
        $sample->removeDirectory("messages");
        $this->assertDirectoryDoesNotExist($sample->path("messages"));
    }

    private function arrayOfPaths(array $paths, Storage $storage)
    {
        return array_map(fn($e) => Utils::joinPath($storage->getRoot(), $e), $paths);
    }

    public function test_exploreDirectory()
    {
        $storage = $this->getSampleStorage(true);

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

        $this->assertEquals($ALL, $storage->exploreDirectory(mode: Storage::NO_FILTER));
        $this->assertEquals($FILES, $storage->exploreDirectory(mode: Storage::ONLY_FILES));
        $this->assertEquals($DIRS, $storage->exploreDirectory(mode: Storage::ONLY_DIRS));
    }

    public function test_listFiles()
    {
        $storage = $this->getSampleStorage(true);
        $this->assertEquals($this->arrayOfPaths(["a.txt", "b.txt"], $storage), $storage->listFiles());
        $this->assertEquals($this->arrayOfPaths(["dir/c.txt", "dir/d.txt"], $storage), $storage->listFiles("dir"));
        $this->assertEquals($this->arrayOfPaths(["dir/subdir/e.txt", "dir/subdir/f.txt"], $storage), $storage->listFiles("dir/subdir"));
    }

    public function test_listDirectories()
    {
        $storage = $this->getSampleStorage(true);
        $this->assertEquals($this->arrayOfPaths(["dir"], $storage), $storage->listDirectories());
        $this->assertEquals($this->arrayOfPaths(["dir/subdir"], $storage), $storage->listDirectories("dir"));
    }

    public function test_isEmpty()
    {
        $storage = $this->getSampleStorage();

        $storage->write("a.txt", "A");
        $storage->makeDirectory("A");

        $this->assertFalse($storage->isEmpty());
        $storage->removeDirectory("A");

        $this->assertFalse($storage->isEmpty());
        $storage->unlink("a.txt");

        $this->assertTrue($storage->isEmpty());
    }
}