<?php

namespace Sharp\Classes\Env\Classes;

use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Storage;

/**
 * This class purpose is to represent a cached object,
 * useful methods are :
 * - `self::fromFile($path)` create an instance from an existent file
 * - `getContent() ` return the un-serialized content
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

    protected ?string $baseMD5 = null;

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

        $this->baseMD5 = $file ? md5_file($this->file) : null;
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

        if ($timeToLive == Cache::PERMANENT)
            return new self($key, $timeToLive, $creationDate, $path);

        if ($creationDate + $timeToLive <= time())
        {
            unlink($path);
            return null;
        }

        return new self($key, $timeToLive, $creationDate, $path);
    }

    /**
     * @return mixed Cache element's content (un-serialized object)
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
        $this->timeToLive = $timeToLive ?? $this->timeToLive;
        $this->content = $content;
    }

    /**
     * @return mixed Return a reference to the content object, which can be edited
     */
    public function &getReference(): mixed
    {
        if (!$this->content)
            $this->getContent();

        return $this->content;
    }

    /**
     * @return bool `true` if the element was edited, `false` otherwise
     */
    public function wasEdited(): bool
    {
        return $this->baseMD5 ?
            $this->baseMD5 === md5(serialize($this->content)) :
            true;
    }

    /**
     * Save the file if needed, otherwise it won't do anything
     * @param Storage $storage Storage to save the file in
     * @return ?string Saved file path or null if not saved
     */
    public function save(Storage $storage): ?string
    {
        if (!$this->content)
            return null;

        if (!$this->wasEdited())
            return null;

        $oldFilename = $this->file;
        $filename = join("_", [$this->creationDate, $this->timeToLive, $this->key]);

        // If the timeToLive or creationDate has changed,
        // we delete the old file to avoid duplicate keys
        if ($oldFilename && (basename($oldFilename) !== $filename))
            unlink($oldFilename);

        $serialized = serialize($this->content);
        $storage->write($filename, $serialized);

        $this->file = $storage->path($filename);
        $this->baseMD5 = md5_file($this->file);

        return $this->file;
    }

    /**
     * Disable the element and delete the source file (if any)
     */
    public function delete(): void
    {
        $this->content = null;

        if ($this->file)
            unlink($this->file);

        $this->file = null;
    }
}