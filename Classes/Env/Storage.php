<?php

namespace Sharp\Classes\Env;

use Exception;
use RuntimeException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Data\ObjectArray;
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

        if (is_dir($this->root))
            return;

        if (!mkdir($this->root, recursive:true))
            throw new RuntimeException("Cannot create [$this->root] directory !");
    }

    public function assertIsWritable(string $path="/"): void
    {
        $path = $this->path($path);
        if (!is_writable($path))
            throw new RuntimeException("[$path] is not writable !");
    }

    public function __destruct()
    {
        ObjectArray::fromArray($this->openedStreams)
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
    public function getNewStorage(string $path): self
    {
        return new self($this->path($path));
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
        if (str_contains($path, $this->root))
            return $path;

        return Utils::joinPath($this->root, $path);
    }

    /**
     * Make a new directory in your main Storage
     *
     * @param string $path Relative path of the new directory (relative to the Storage root)
     */
    public function makeDirectory(string $path): void
    {
        $path = $this->path($path);
        if (!is_dir($path))
            mkdir($path, recursive: true);
    }

    /**
     * Get a resource (create it if needed)
     *
     * @param string $path Relative name (relative to the Storage root)
     * @param string $mode Mode for `fopen()`
     * @param bool $autoclose If `true`, the storage will close returned stream on desctruct
     * @return resource Opened resource
     * @link https://www.php.net/manual/en/function.fopen.php
     */
    public function getStream(string $path, string $mode="r", bool $autoclose=true)
    {
        $path = $this->path($path);

        if (!($stream = fopen($path, $mode)))
            throw new RuntimeException("Could not open [$path] with mode [$mode]");

        if ($autoclose)
            $this->openedStreams[] = &$stream;

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

        $directory = dirname($path);
        $this->makeDirectory($directory);
        $this->assertIsWritable($directory);

        file_put_contents($path, $content, $flags);
    }

    /**
     * @param string File's to read relative path (relative to the Storage root)
     * @return string File's content
     */
    public function read(string $path): string
    {
        $path = $this->path($path);
        return file_get_contents($path);
    }

    /**
     * @param string File to check (relative to the Storage root)
     * @return bool `true` if the target is a file, `false` otherwise
     */
    public function isFile(string $path): bool
    {
        return is_file($this->path($path));
    }

    /**
     * @param string Directory to check (relative to the Storage root)
     * @return bool `true` if the target is a directory, `false` otherwise
     */
    public function isDirectory(string $path): bool
    {
        return is_dir($this->path($path));
    }

    /**
     * @return `true` if given path is empty, `false` otherwise
     */
    public function isEmpty(string $path="/"): bool
    {
        if (!$this->isDirectory($path))
            throw new Exception("[$path] is not a directory");

        return count(scandir($this->path($path))) == 2; // Includes only "." and ".."
    }

    /**
     * @param string File to unlink (relative to the Storage root)
     * @return bool `true` on success, `false` on failure
     */
    public function unlink(string $path): bool
    {
        $path = $this->path($path);
        return is_file($path) ?
            unlink($path):
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
        return is_dir($path) ?
            rmdir($path):
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
        $path = $this->path($path);
        return Utils::exploreDirectory($path, $mode);
    }

    /**
     * List direct files in a directory
     *
     * @param string $path Path to list (relative to the Storage root)
     * @return array List of direct files in given directory
     */
    public function listFiles(string $path="/"): array
    {
        $path = $this->path($path);
        return Utils::listFiles($path);
    }

    /**
     * List direct directories in a directory
     *
     * @param string $path Path to list (relative to the Storage root)
     * @return array List of direct directories in given directory
     */
    public function listDirectories(string $path="/"): array
    {
        $path = $this->path($path);
        return Utils::listDirectories($path);
    }
}