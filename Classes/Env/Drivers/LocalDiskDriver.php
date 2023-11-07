<?php

namespace Sharp\Classes\Env\Drivers;

use Sharp\Classes\Data\ObjectArray;
use Sharp\Core\Utils;

class LocalDiskDriver implements FileDriverInterface
{
    public function isFile(string $path)
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function makeDirectory(string $directory)
    {
        mkdir($directory, recursive:true);
    }

    public function directoryName(string $path): string
    {
        return dirname($path);
    }

    public function openFile(string $path, string $mode): mixed
    {
        return fopen($path, $mode);
    }

    public function filePutContents(string $path, mixed $content, int $flags=0)
    {
        file_put_contents($path, $content, $flags);
    }

    public function fileGetContents(string $path): string
    {
        return file_get_contents($path);
    }

    public function scanDirectory(string $path): array
    {
        return ObjectArray::fromArray(array_diff(scandir($path), [".", ".."]))
        ->map(fn($x) => Utils::joinPath($path, $x))
        ->collect();
    }

    public function removeFile(string $path): bool
    {
        return unlink($path);
    }

    public function removeDirectory(string $path): bool
    {
        return rmdir($path);
    }

    /**
     * Explore a directory and return a list of absolutes Directory/Files paths
     * A filter can be used to edit the results:
     * - `Utils::ONLY_DIRS` : Only return names of (sub)directories
     * - `Utils::ONLY_FILES` : Only return names of (sub)files
     */
    public function exploreDirectory(string $path, int $mode=self::NO_FILTER): array
    {
        $results = [];
        foreach ($this->scanDirectory($path) as $file)
        {
            if (!$this->isFile($file))
            {
                if ($mode !== self::ONLY_FILES)
                    $results[] = $file;

                array_push($results, ...self::exploreDirectory($file, $mode));
            }
            else if ($mode !== self::ONLY_DIRS)
            {
                $results[] = $file;
            }
        }
        return $results;
    }

    /**
     * @param string $directory Directory to scan
     * @return array Absolute path from direct FILES
     */
    public function listFiles(string $directory): array
    {
        return ObjectArray::fromArray($this->scanDirectory($directory))
        ->filter($this->isFile(...))
        ->collect();
    }

    /**
     * @param string $directory Directory to scan
     * @return array Absolute path from direct DIRECTORIES
     */
    public function listDirectories(string $directory): array
    {
        return ObjectArray::fromArray($this->scanDirectory($directory))
        ->filter(fn($file) => !$this->isFile($file))
        ->collect();
    }
}