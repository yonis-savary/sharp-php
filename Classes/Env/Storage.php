<?php

namespace Sharp\Classes\Env;

use RuntimeException;
use Sharp\Classes\Core\Component;
use Sharp\Core\Utils;

class Storage
{
    use Component;

    /** Used for `exploreDirectory()` to return both directories and files */
    const NO_FILTER = 0;
    /** Used for `exploreDirectory()` to only return directories */
    const ONLY_DIRS = 1;
    /** Used for `exploreDirectory()` to only return files */
    const ONLY_FILES = 2;

    protected array $openedStreams = [];
    protected string $root;

    public static function getDefaultInstance()
    {
        return new self(Utils::relativePath("Storage"));
    }

    /**
     * @param string $root Root directory of the new storage, every path will be relative to this one
     */
    public function __construct(string $root)
    {
        $this->root = $root;

        if (!is_dir($this->root))
            mkdir($this->root, recursive:true);

        $this->makeDirectory($root);
    }

    public function assertIsWritable(string $path=null)
    {
        $path = $this->path($path ?? "/");
        if (!is_writable($path))
            throw new RuntimeException("[$path] is not writable !");
    }

    public function __destruct()
    {
        $toClose = array_filter($this->openedStreams);
        foreach ($toClose as $stream)
            fclose($stream);
    }

    /**
     * @return string Get the root directory of the Storage
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * @return static Get a new Storage object from a subdirectory
     */
    public function getNewStorage(string $path): self
    {
        return new self($this->path($path));
    }

    /**
     * @param string Absolute/Relative path to adapt
     * @return string Adapted path relative to the Storage root directory
     */
    public function path(string $path): string
    {
        if (str_contains($path, $this->root))
            return $path;

        return Utils::joinPath($this->root, $path);
    }

    public function makeDirectory(string $name): void
    {
        $name = $this->path($name);
        if (!is_dir($name))
            mkdir($name, recursive: true);
    }

    /**
     * @param string $path Relative/Absolute path to open
     * @param string $mode fopen() mode
     * @param bool $autoclose If `true`, you don't need to close requested resource manually
     * @return resource
     */
    public function getStream(string $path, string $mode="r", bool $autoclose=true)
    {
        $path = $this->path($path);
        $stream = fopen($path, $mode);
        if (!$stream)
            throw new RuntimeException("Could not open [$path] with mode [$mode]");

        if ($autoclose)
            $this->openedStreams[] = &$stream;

        return $stream;
    }

    /**
     * @param string $path Relative/Absolute path to write
     * @param string $content Content to write
     * @param int $flags file_put_contents() flags
     */
    public function write(string $path, string $content, int $flags=0): void
    {
        $path = $this->path($path);

        $directory = dirname($path);
        $this->makeDirectory($directory);
        $this->assertIsWritable($directory);

        file_put_contents($path, $content, $flags);
    }

    public function read(string $path): string
    {
        $path = $this->path($path);
        return file_get_contents($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($this->path($path));
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($this->path($path));
    }

    public function unlink(string $path): bool
    {
        $path = $this->path($path);
        return is_file($path) ?
            unlink($path):
            true;
    }

    public function removeDirectory(string $path): bool
    {
        $path = $this->path($path);
        return is_dir($path) ?
            rmdir($path):
            true;
    }

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