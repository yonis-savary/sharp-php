<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Env\Configuration;
use Sharp\Classes\Env\Drivers\FTPDriver;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;
use Throwable;

class FTPStorageTest extends TestCase
{
    protected ?bool $enabled = null;
    protected $config = null;
    protected Storage $storage;
    protected Storage $ftp;


    protected function createFTPStorage()
    {
        $ftpDir = uniqid("ftp-test-");

        $ftpDirPath = Storage::getInstance()->path($ftpDir);
        mkdir($ftpDirPath, 777);

        $this->storage = $storage = Storage::getInstance()->getSubStorage($ftpDir);

        $storage->makeDirectory("SOME-DIR");
        $storage->write("SOME-FILE", "test");

        $this->ftp = new Storage(
            $this->storage->getRoot(),
            new FTPDriver(
                "localhost",
                $this->config["username"],
                $this->config["password"],
                $this->config["port"] ?? 21,
            )
        );
    }

    protected function isTestSuiteEnabled()
    {
        if ($this->enabled === false)
            return false;

        if ($this->enabled === true)
            return true;

        $this->enabled = false;
        if ($this->config = Configuration::getInstance()->try("ftp-test"))
        {
            $this->enabled = true;

            try
            {
                $this->createFTPStorage();
            }
            catch (Throwable $err)
            {
                Logger::getInstance()->logThrowable($err);
                $this->enabled = false;
            }
        }
        else
        {
            Logger::getInstance()->info("FTP Test not enabled");
        }

        return $this->enabled;
    }

    public function test_isFile()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $this->assertFalse($this->ftp->isFile("INEXISTENT"));
        $this->assertFalse($this->ftp->isFile("SOME-DIR"));
        $this->assertTrue($this->ftp->isFile("SOME-FILE"));
    }

    public function test_isDirectory()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $this->assertFalse($this->ftp->isFile("INEXISTENT"));
        $this->assertTrue($this->ftp->isFile("SOME-DIR"));
        $this->assertFalse($this->ftp->isFile("SOME-FILE"));
    }



    public function test_makeDirectory()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);


        $this->ftp->makeDirectory("DIR");
        $this->ftp->makeDirectory("DIR/SUBDIR");

        $this->assertTrue($this->storage->isDirectory("DIR"));
        $this->assertTrue($this->storage->isDirectory("DIR/SUBDIR"));
    }



    public function test_filePutContents()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $this->ftp->write("put-content.txt", "Hello!");

        $this->assertEquals(
            "Hello!",
            $this->storage->read("put-content.txt")
        );
    }

    public function test_fileGetContents()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $this->storage->write("Hello.txt", "Goodbye");

        $this->assertEquals(
            "Goodbye",
            $this->ftp->read("Hello.txt")
        );
    }

    public function test_removeFile()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $this->storage->write("TO-DELETE", 0);
        $this->ftp->unlink("TO-DELETE");
        $this->assertFalse($this->storage->isFile("TO-DELETE"));
    }

    public function test_removeDirectory()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $this->storage->makeDirectory("DIR-TO-DELETE");
        $this->ftp->removeDirectory("DIR-TO-DELETE");

        $this->assertFalse($this->storage->isDirectory("DIR-TO-DELETE"));
    }


    public function test_exploreDirectory()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $files = [
            "EXPLORE/A.txt",
            "EXPLORE/B.txt"
        ];

        $dirs = [
            "EXPLORE",
            "EXPLORE/SUBSCAN"
        ];

        $makeFullPath = fn($file) => Utils::joinPath($this->storage->getRoot(), $file);

        $files = ObjectArray::fromArray($files)->map($makeFullPath)->collect();
        $dirs  = ObjectArray::fromArray($dirs)->map($makeFullPath)->collect();

        $this->storage->makeDirectory("EXPLORE");
        $this->storage->makeDirectory("EXPLORE/SUBSCAN");
        $this->storage->write("EXPLORE/A.txt", 0);
        $this->storage->write("EXPLORE/B.txt", 0);

        $this->assertEquals($files, $this->ftp->exploreDirectory("/", Utils::ONLY_FILES));
        $this->assertEquals($dirs, $this->ftp->exploreDirectory("/", Utils::ONLY_DIRS));
        $this->assertEquals(array_merge($dirs, $files), $this->ftp->exploreDirectory());
    }

    public function test_listFiles()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $this->storage->makeDirectory("LIST-FILES");
        $this->storage->makeDirectory("LIST-FILES/SUBSCAN");
        $this->storage->write("LIST-FILES/A.txt", 0);
        $this->storage->write("LIST-FILES/B.txt", 0);
        $this->storage->write("LIST-FILES/SUBDIR/C.txt", 0);

        $files = ObjectArray::fromArray([
            "LIST-FILES/A.txt",
            "LIST-FILES/B.txt",
        ])
        ->map(fn($file) => Utils::joinPath($this->storage->getRoot(), $file))
        ->collect();

        $this->assertEquals($files, $this->ftp->listFiles());
    }

    public function test_listDirectories()
    {
        if (!$this->isTestSuiteEnabled())
            return $this->assertTrue(true);

        $this->storage->makeDirectory("LIST-FILES");
        $this->storage->makeDirectory("LIST-FILES/SUBSCAN");
        $this->storage->write("LIST-FILES/A.txt", 0);
        $this->storage->write("LIST-FILES/B.txt", 0);
        $this->storage->write("LIST-FILES/SUBDIR/C.txt", 0);

        $files = ObjectArray::fromArray([
            "LIST-FILES/SUBSCAN",
            "LIST-FILES/SUBDIR",
        ])
        ->map(fn($file) => Utils::joinPath($this->storage->getRoot(), $file))
        ->collect();

        $this->assertEquals($files, $this->ftp->listFiles());
    }
}