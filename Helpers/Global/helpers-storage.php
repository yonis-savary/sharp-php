<?php

use Sharp\Classes\Env\Storage;

/**
 * Shortcut to `Storage::getInstance()->getSubStorage()`
 *
 * Get a new `Storage` object from a relative path
 *
 * @param string $path Relative path to get the Storage of (relative to your main Storage directory)
 * @return Storage New storage with the $path as root
 */
function storeGetSubStorage(string $path): Storage
{
    return Storage::getInstance()->getSubStorage($path);
}

/**
 * Shortcut to `Storage::getInstance()->path()`
 *
 * Get an absolute path from a relative path
 *
 * @param string $path Relative path to get (relative to your main Storage directory)
 * @return string Absolute path from given relative path
 */
function storePath(string $path): string
{
    return Storage::getInstance()->path($path);
}

/**
 * Shortcut to `Storage::getInstance()->makeDirectory()`
 *
 * Make a new directory in your main Storage
 *
 * @param string $name Relative path of the new directory (relative to your main Storage directory)
 */
function storeMakeDirectory(string $path): void
{
    Storage::getInstance()->makeDirectory($path);
}

/**
 * Shortcut to `Storage::getInstance()->getStream()`
 *
 * Get a resource (create it if needed)
 *
 * @param string $path Relative name (relative to your main Storage directory)
 * @param string $mode Mode for `fopen()`
 * @param bool $autoclose If `true`, the storage will close returned stream on desctruct
 * @return resource Opened resource
 * @link https://www.php.net/manual/en/function.fopen.php
 */
function storeGetStream(string $path, string $mode="r", bool $autoclose=true): mixed
{
    return Storage::getInstance()->getStream($path, $mode, $autoclose);
}

/**
 * Shortcut to `Storage::getInstance()->write()`
 *
 * @param string $path Relative path to write (relative to your main Storage directory)
 * @param string $content Content to write
 * @param int $flags Flags for `file_put_contents()`
 * @link https://www.php.net/manual/en/function.file-put-contents.php
 */
function storeWrite(string $path, string $content, int $flags=0): void
{
    Storage::getInstance()->write($path, $content, $flags);
}

/**
 * Shortcut to `Storage::getInstance()->read()`
 *
 * @param string File's to read relative path (relative to your main Storage directory)
 * @return string File's content
 */
function storeRead(string $path): string
{
    return Storage::getInstance()->read($path);
}

/**
 * Shortcut to `Storage::getInstance()->isFile()`
 *
 * @param string File to check (relative to your main Storage directory)
 * @return bool `true` if the target is a file, `false` otherwise
 */
function storeIsFile(string $path): bool
{
    return Storage::getInstance()->isFile($path);
}

/**
 * Shortcut to `Storage::getInstance()->isDirectory()`
 *
 * @param string Directory to check (relative to your main Storage directory)
 * @return bool `true` if the target is a directory, `false` otherwise
 */
function storeIsDirectory(string $path): bool
{
    return Storage::getInstance()->isDirectory($path);
}

/**
 * Shortcut to `Storage::getInstance()->unlink()`
 *
 * @param string File to unlink (relative to your main Storage directory)
 * @return bool `true` on success, `false` on failure
 */
function storeUnlink(string $path): bool
{
    return Storage::getInstance()->unlink($path);
}

/**
 * Shortcut to `Storage::getInstance()->removeDirectory()`
 *
 * Remove an EMPTY directory
 *
 * @param string Directory to remove (relative to your main Storage directory)
 */
function storeRemoveDirectory(string $path): bool
{
    return Storage::getInstance()->removeDirectory($path);
}

/**
 * Shortcut to `Storage::getInstance()->exploreDirectory()`
 *
 * Explore a directory and return every sub-dir/files absolute path
 *
 * @param string Directory to explore (relative to your main Storage directory)
 * @param int $mode `Storage::NO_FILTER|ONLY_DIR|ONLY_FILES` flag to filter the results
 * @return array List of absolute sub-dirs/files paths (unless filtered with `$mode`)
 */
function storeExploreDirectory(string $path, int $mode=Storage::NO_FILTER): array
{
    return Storage::getInstance()->exploreDirectory($path, $mode);
}

/**
 * Shortcut to `Storage::getInstance()->listFiles()`
 *
 * List direct files in a directory
 *
 * @param string $path Path to list (relative to your main Storage directory)
 * @return array List of direct files in given directory
 */
function storeListFiles(string $path="/"): array
{
    return Storage::getInstance()->listFiles($path);
}

/**
 * Shortcut to `Storage::getInstance()->listDirectories()`
 *
 * List direct directories in a directory
 *
 * @param string $path Path to list (relative to your main Storage directory)
 * @return array List of direct directories in given directory
 */
function storeListDirectories(string $path="/"): array
{
    return Storage::getInstance()->listDirectories($path);
}

