<?php

namespace Sharp\Classes\Env;

use RuntimeException;
use Sharp\Classes\Core\Component;
use Sharp\Core\Utils;

class Storage
{
    use Component;

    protected array $streamToClose = [];

    protected string $root;

    public static function getDefaultInstance()
    {
        return new self(Utils::relativePath("Storage"));
    }

    public function __construct(string $root)
    {
        $this->root = $root;
        $this->makeDirectory($root);
    }

    public function __destruct()
    {
        foreach ($this->streamToClose as $stream)
        {
            if ($stream)
                fclose($stream);
        }
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function getNewStorage(string $path): self
    {
        return new self($this->path($path));
    }

    public function path(string $path): string
    {
        if (str_contains($path, $this->root))
            return $path;

        return Utils::joinPath($this->root, $path);
    }

    public function makeDirectory(string $name)
    {
        $name = $this->path($name);
        if (!is_dir($name))
            mkdir($name, recursive: true);
    }

    /**
     * @return resource
     */
    public function getStream(string $path, string $mode="r", bool $autoclose=true)
    {
        $path = $this->path($path);
        $stream = fopen($path, $mode);
        if (!$stream)
            throw new RuntimeException("Could not open [$path]");

        if ($autoclose)
            $this->streamToClose[] = &$stream;

        return $stream;
    }

    public function write(string $path, string $content, int $flags=0)
    {
        $path = $this->path($path);
        $this->makeDirectory(dirname($path));

        file_put_contents($path, $content, $flags);
    }

    public function read(string $path)
    {
        $path = $this->path($path);
        return file_get_contents($path);
    }

    public function isFile(string $path)
    {
        return is_file($this->path($path));
    }

    public function isDirectory(string $path)
    {
        return is_dir($this->path($path));
    }

    public function unlink(string $path)
    {
        $path = $this->path($path);
        if (is_file($path))
            return unlink($path);
        return true;
    }

    public function removeDirectory(string $path)
    {
        $path = $this->path($path);
        if (is_dir($path))
            return rmdir($path);
        return true;
    }






    const NO_FILTER = 0;
    const ONLY_DIRS = 1;
    const ONLY_FILES = 2;

    public function exploreDirectory(string $path="/", int $mode=self::NO_FILTER)
    {
        $path = $this->path($path);
        return Utils::exploreDirectory($path, $mode);
    }


    public function listFiles(string $path="/")
    {
        $path = $this->path($path);
        return Utils::listFiles($path);
    }

    public function listDirectories(string $path="/")
    {
        $path = $this->path($path);
        return Utils::listDirectories($path);
    }
}