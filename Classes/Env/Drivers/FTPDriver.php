<?php

namespace Sharp\Classes\Env\Drivers;

use Exception;
use RuntimeException;
use FTP\Connection;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;

class FTPDriver implements FileDriverInterface
{
    private Connection $handler;

    public function __construct(string $url, string $username, string $password, int $port=21, int $timeout=90, bool $passiveMode=true)
    {
        if (! $handler = ftp_connect($url, $port, $timeout))
            throw new RuntimeException("FTP - Could not connect to [$url:$port]");

        $this->handler = $handler;
        if (!ftp_login($this->handler, $username, $password))
            throw new RuntimeException("Could not login to [$url:$port] as [$username]");

        ftp_raw($this->handler, 'OPTS UTF8 ON');
        ftp_pasv($this->handler, $passiveMode);
    }

    public function __destruct()
    {
        if ($this->handler)
            ftp_close($this->handler);
    }

    public function isFile(string $path)
    {
        return ftp_size($this->handler, $path) != -1;
    }

    public function isDirectory(string $path): bool
    {
        if (is_array(ftp_nlist($this->handler, $path)))
            return true;

        return false;
    }

    public function isWritable(string $path): bool
    {
        $tmpFile = Utils::joinPath($path, uniqid("ftp-is-writable-"));

        $this->filePutContents($tmpFile, 0);
        if ($writable = $this->isFile($tmpFile))
            $this->removeFile($tmpFile);

        return $writable;
    }

    public function makeDirectory(string $directory)
    {
        ftp_mkdir($this->handler, $directory);
    }

    public function scanDirectory(string $path): array
    {
        if (!$res = ftp_nlist($this->handler, $path))
            return [];


        debug($res);

        return $res;
    }

    public function directoryName(string $path): string
    {
        return dirname($path);
    }

    public function openFile(string $path, string $mode): mixed
    {
        return false;
    }

    public function filePutContents(string $path, mixed $content, int $flags=0)
    {
        if ($flags)
            Logger::getInstance()->warning("FTP filePutContents does not support flags (got $flags)");

        Storage::getInstance()->makeDirectory("tmp");

        $tmpFile = Storage::getInstance()->path("tmp/".uniqid("ftp-"));
        file_put_contents($tmpFile, $content);

        if (!ftp_alloc($this->handler, filesize($tmpFile), $result))
            throw new Exception("Could not allocate enough memory to write file (reason: $result)");

        ftp_put($this->handler, $path, $tmpFile);
        unlink($tmpFile);
    }

    public function fileGetContents(string $path): string
    {
        Storage::getInstance()->makeDirectory("tmp");

        $tmpFile = Storage::getInstance()->path("tmp/".uniqid("ftp-"));
        ftp_get($this->handler, $tmpFile, $path);

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);
        return $content;
    }

    public function removeFile(string $path): bool
    {
        return ftp_delete($this->handler, $path);
    }

    public function removeDirectory(string $path): bool
    {
        return ftp_rmdir($this->handler, $path);
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
