<?php

namespace Sharp\Classes\Env;

use Exception;
use RuntimeException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Env\Drivers\FileDriverInterface;
use Sharp\Classes\Env\Drivers\LocalDiskDriver;
use Sharp\Core\Utils;

class Storage
{
    use Component;

    /** Used for `exploreDirectory()` to return both directories and files */
    const NO_FILTER = FileDriverInterface::NO_FILTER;
    /** Used for `exploreDirectory()` to only return directories */
    const ONLY_DIRS = FileDriverInterface::ONLY_DIRS;
    /** Used for `exploreDirectory()` to only return files */
    const ONLY_FILES = FileDriverInterface::ONLY_FILES;

    protected array $openedStreamsToClose = [];
    protected string $root;

    protected FileDriverInterface $driver;

    public static function getDefaultInstance()
    {
        return new self(Utils::relativePath("Storage"));
    }

    /**
     * @param string $root Root directory of the new storage, every path will be relative to this one
     */
    public function __construct(string $root, FileDriverInterface $driver=null)
    {
        $this->driver = $driver ?? new LocalDiskDriver();

        $this->root = Utils::normalizePath($root);
        $this->makeDirectory("/");

        if (!$this->driver->isDirectory($this->root))
            throw new RuntimeException("Cannot create [$this->root] directory !");
    }

    public function assertIsWritable(string $path="/"): void
    {
        $path = $this->path($path);
        if (!$this->driver->isWritable($path))
            throw new RuntimeException("[$path] is not writable !");
    }

    public function __destruct()
    {
        ObjectArray::fromArray($this->openedStreamsToClose)
        ->filter()
        ->foreach(fn($stream) => fclose($stream));
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
    public function getSubStorage(string $path): self
    {
        return new self($this->path($path), $this->driver);
    }

    /**
     * Make a relative path from a given path part (Relative of absolute to the storage)
     *
     * @param string $path Relative path to get (relative to the Storage root)
     * @return string Absolute path from given relative path
     * @note If an absolute path is given, it is returned directly
     */
    public function path(string $path): string
    {
        return Utils::relativePath($path, $this->root);
    }

    /**
     * Make a new directory in your main Storage
     *
     * @param string $path Relative path of the new directory (relative to the Storage root)
     */
    public function makeDirectory(string $path): void
    {
        $path = $this->path($path);

        if (!$this->driver->isDirectory($path))
            $this->driver->makeDirectory($path);
    }

    /**
     * Get a resource (create it if needed)
     *
     * @param string $path Relative name (relative to the Storage root)
     * @param string $mode Mode for `fopen()`
     * @param bool $autoClose If `true`, the storage will close returned stream on destruct
     * @return resource Opened resource
     * @link https://www.php.net/manual/en/function.fopen.php
     */
    public function getStream(string $path, string $mode="r", bool $autoClose=true)
    {
        $path = $this->path($path);
        $this->makeDirectory($this->driver->directoryName($path));

        if (! $stream = $this->driver->openFile($path, $mode))
            throw new RuntimeException("Could not open [$path] with mode [$mode]");

        if ($autoClose)
            $this->openedStreamsToClose[] = &$stream;

        return $stream;
    }

    /**
     * @param string $path Relative path to write (relative to the Storage root)
     * @param string $content Content to write
     * @param int $flags Flags for `file_put_contents()`
     * @link https://www.php.net/manual/en/function.file-put-contents.php
     */
    public function write(string $path, string $content, int $flags=0): void
    {
        $path = $this->path($path);

        $directory = $this->driver->directoryName($path);
        $this->makeDirectory($directory);
        $this->assertIsWritable($directory);

        $this->driver->filePutContents($path, $content, $flags);
    }

    /**
     * @param string File's to read relative path (relative to the Storage root)
     * @return string File's content
     */
    public function read(string $path): string
    {
        $path = $this->path($path);
        return $this->driver->fileGetContents($path);
    }

    /**
     * @param string File to check (relative to the Storage root)
     * @return bool `true` if the target is a file, `false` otherwise
     */
    public function isFile(string $path): bool
    {
        return $this->driver->isFile($this->path($path));
    }

    /**
     * @param string Directory to check (relative to the Storage root)
     * @return bool `true` if the target is a directory, `false` otherwise
     */
    public function isDirectory(string $path): bool
    {
        return $this->driver->isDirectory($this->path($path));
    }

    /**
     * @return `true` if given path is empty, `false` otherwise
     */
    public function isEmpty(string $path="/"): bool
    {
        if (!$this->isDirectory($path))
            throw new Exception("[$path] is not a directory");

        $path = $this->path($path);
        $content = $this->driver->scanDirectory($path);

        return count($content) === 0;
    }

    /**
     * @param string File to unlink (relative to the Storage root)
     * @return bool `true` on success, `false` on failure
     */
    public function unlink(string $path): bool
    {
        $path = $this->path($path);
        return $this->driver->isFile($path) ?
            $this->driver->removeFile($path):
            true;
    }

    /**
     * Remove an EMPTY directory
     *
     * @param string Directory to remove (relative to the Storage root)
     */
    public function removeDirectory(string $path): bool
    {
        $path = $this->path($path);
        return $this->driver->isDirectory($path) ?
            $this->driver->removeDirectory($path):
            true;
    }

    /**
     * Explore a directory and return every sub-dir/files absolute path
     *
     * @param string Directory to explore (relative to the Storage root)
     * @param int $mode `Storage::NO_FILTER|ONLY_DIR|ONLY_FILES` flag to filter the results
     * @return array List of absolute sub-dirs/files paths (unless filtered with `$mode`)
     */
    public function exploreDirectory(string $path="/", int $mode=self::NO_FILTER): array
    {
        return $this->driver->exploreDirectory($this->path($path), $mode);
    }

    /**
     * List direct files in a directory
     *
     * @param string $path Path to list (relative to the Storage root)
     * @return array List of direct files in given directory
     */
    public function listFiles(string $path="/"): array
    {
        return $this->driver->listFiles($this->path($path));
    }

    /**
     * List direct directories in a directory
     *
     * @param string $path Path to list (relative to the Storage root)
     * @return array List of direct directories in given directory
     */
    public function listDirectories(string $path="/"): array
    {
        return $this->driver->listDirectories($this->path($path));
    }
}