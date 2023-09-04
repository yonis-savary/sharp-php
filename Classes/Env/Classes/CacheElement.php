<?php

namespace Sharp\Classes\Env\Classes;

use Sharp\Classes\Env\Storage;

/**
 * This class purpose is to represent a cached object,
 * useful methods are :
 * - `self::fromFile($path)` create an instance from an existant file
 * - `getContent() ` return the unserialized content
 * - `setContent()` edit the object's content
 * - `save()` save the file if needed
 */
class CacheElement
{
    public readonly string $key;
    protected int $creationDate;
    protected int $timeToLive;
    protected ?string $file;
    protected mixed $content = null;

    protected bool $edited = false;

    public function __construct(
        string $key,
        int $timeToLive=3600,
        ?int $creationDate=null,
        ?string $file=null,
    ){
        $this->key = $key;
        $this->creationDate = $creationDate ?? time();
        $this->timeToLive = $timeToLive;
        $this->file = $file;
    }

    /**
     * Give this method a file to create a new object,
     * if the file is invalid (expired), it is deleted and `null` is returned
     */
    public static function fromFile(string $path): null|self
    {
        list($creationDate, $timeToLive, $key) = explode("_", basename($path), 3);

        $creationDate = intval($creationDate);
        $timeToLive = intval($timeToLive);

        if ($timeToLive != 0 && ($creationDate + $timeToLive <= time()))
        {
            unlink($path);
            return null;
        }

        return new self($key, $timeToLive, $creationDate, $path);
    }

    /**
     * Return the cache element content (unserialized object)
     */
    public function getContent(): mixed
    {
        if ($this->content)
            return $this->content;

        if ($this->file)
            $this->content = unserialize(file_get_contents($this->file));

        return $this->content;
    }

    public function setContent(mixed $content, int $timeToLive=null): void
    {
        $this->edited = true;
        $this->timeToLive = $timeToLive ?? $this->timeToLive;
        $this->creationDate = time();
        $this->content = $content;
    }

    /**
     * Save the file if needed, otherwise it won't do anything
     * @param Storage $storage Storage to save the file in
     * @return ?string Saved file path or null if not saved
     */
    public function save(Storage $storage): ?string
    {
        if ($this->file && (!$this->edited))
            return null;

        if (!$this->content)
            return null;

        $filename = join("_", [$this->creationDate, $this->timeToLive, $this->key]);

        $storage->write(
            $filename,
            serialize($this->content)
        );

        return $storage->path($filename);
    }

    public function delete(): void
    {
        $this->content = null;
        $this->edited = false;

        if ($this->file)
            unlink($this->file);
    }
}