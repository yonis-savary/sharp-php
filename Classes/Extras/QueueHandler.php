<?php

namespace Sharp\Classes\Extras;

use Sharp\Classes\Core\Logger;
use Sharp\Classes\Env\Storage;
use Throwable;

trait QueueHandler
{
    final protected static function pushQueueItem(array $data): string
    {
        $filename = uniqid(time() . "-");

        $storage = self::getQueueStorage();
        $storage->write($filename, serialize($data));

        return $storage->path($filename);
    }

    final public static function getQueueStorage(): Storage
    {
        $thisClassHash = md5(self::class);
        return Storage::getInstance()->getSubStorage("Queue/$thisClassHash");
    }

    final public static function processQueue(): void
    {
        $storage = self::getQueueStorage();
        $logger = self::getQueueProcessingLogger();

        $files = $storage->listFiles();

        $toProcess = array_slice($files, 0, self::getQueueProcessCapacity());
        $logger->info(self::class, "Processing ". count($toProcess) ." items");

        foreach ($toProcess as $file)
        {
            $rawData = file_get_contents($file);

            try
            {
                $data = unserialize($rawData);
            }
            catch(Throwable $err)
            {
                $logger->error("Could not unserialize data [$file]", $rawData);
                $logger->logThrowable($err);
                continue;
            }

            try
            {
                self::processQueueItem($data);
                unlink($file);
            }
            catch (Throwable $err)
            {
                $logger->info("Could not process queue item !", $data);
                $logger->logThrowable($err);
                continue;
            }
        }
    }

    public static function getQueueProcessCapacity(): int
    {
        return 10;
    }

    protected static function getQueueProcessingLogger(): Logger
    {
        return Logger::getInstance();
    }

    protected static function processQueueItem(array $data)
    {

    }
}