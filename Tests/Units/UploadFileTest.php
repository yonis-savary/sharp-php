<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Env\Storage;
use Sharp\Classes\Http\Classes\UploadFile;

class UploadFileTest extends TestCase
{
    public static function getDummyPHPUpload(int $withError=null): array
    {
        $randomName = bin2hex(random_bytes(32)).".json";

        $jsonData = [];
        for ($i=0; $i<10; $i++)
            $jsonData[] = random_int(0, 1000);

        $tmpName = Storage::getInstance()->path($randomName);
        file_put_contents($tmpName, $jsonData);

        $data = [
            "name" => $randomName,
            "type" => "application/json",
            "tmp_name" => $tmpName,
            "error" => $withError ?? UPLOAD_ERR_OK,
            "size" => filesize($tmpName),
        ];

        return $data;
    }

    public function test_movable()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertTrue($upload->movable());
        $upload->move(Storage::getInstance()->path("uploads"));
        $this->assertFalse($upload->movable());

        $upload = new UploadFile($this->getDummyPHPUpload(UPLOAD_ERR_NO_FILE));
        $this->assertFalse($upload->movable());
    }

    public function test_move()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertIsString(
            $upload->move(Storage::getInstance()->path("uploads"))
        );

        $upload = new UploadFile($this->getDummyPHPUpload(UPLOAD_ERR_NO_FILE));
        $this->assertFalse(
            $upload->move(Storage::getInstance()->path("uploads"))
        );
    }

    public function test_isMoved()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertFalse($upload->isMoved());
        $upload->move(Storage::getInstance()->path("uploads"));
        $this->assertTrue($upload->isMoved());
    }

    public function test_getName()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertIsString($upload->getName());
    }

    public function test_getTempName()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertFileExists($upload->getTempName());
    }

    public function test_getType()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertEquals("application/json", $upload->getType());
    }

    public function test_getError()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertEquals(UPLOAD_ERR_OK, $upload->getError());

        $upload = new UploadFile($this->getDummyPHPUpload(UPLOAD_ERR_NO_FILE));
        $this->assertEquals(UPLOAD_ERR_NO_FILE, $upload->getError());
    }

    public function test_getSize()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertEquals(filesize($upload->getTempName()), $upload->getSize());
    }

    public function test_getInputName()
    {
        $upload = new UploadFile($this->getDummyPHPUpload(), "someupload");
        $this->assertEquals("someupload", $upload->getInputName());
    }

    public function test_getExtension()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $this->assertEquals("json", $upload->getExtension());
    }

    public function test_getNewName()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $upload->move(Storage::getInstance()->path("uploads"));
        $this->assertIsString($upload->getNewName());
    }

    public function test_getNewPath()
    {
        $upload = new UploadFile($this->getDummyPHPUpload());
        $upload->move(Storage::getInstance()->path("uploads"));
        $this->assertFileExists($upload->getNewPath());
    }

    public function test_getFailReason()
    {
        $upload = new UploadFile($this->getDummyPHPUpload(UPLOAD_ERR_NO_FILE));
        $upload->move(Storage::getInstance()->path("uploads"));
        $this->assertEquals(UploadFile::REASON_PHP_UPLOAD_ERROR, $upload->getFailReason());

        $upload = new UploadFile($this->getDummyPHPUpload());
        $upload->move(Storage::getInstance()->path("uploads"));
        $this->assertEquals(UploadFile::REASON_OK, $upload->getFailReason());
    }
}