<?php

namespace Sharp\Classes\Env\Drivers;

interface FileDriverInterface
{
    /*
        -----------------------------
        Directory/File Utils function
        -----------------------------
    */

    const NO_FILTER = 0;
    const ONLY_DIRS = 1;
    const ONLY_FILES = 2;

    public function isFile(string $path);
    public function isDirectory(string $path): bool;
    public function isWritable(string $path): bool;

    public function makeDirectory(string $directory);
    public function scanDirectory(string $path): array;
    public function directoryName(string $path): string;

    public function openFile(string $path, string $mode): mixed;

    public function filePutContents(string $path, mixed $content, int $flags=0);
    public function fileGetContents(string $path): string;

    public function removeFile(string $path): bool;
    public function removeDirectory(string $path): bool;

    public function exploreDirectory(string $path, int $mode=self::NO_FILTER): array;
    public function listFiles(string $directory): array;
    public function listDirectories(string $directory): array;
}