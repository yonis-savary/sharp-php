<?php

namespace Sharp\Classes\Extras;

use InvalidArgumentException;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;
use Throwable;

trait QueueHandler
{
    final protected static function pushQueueItem(array $data): string
    {
        try
        {
            $serializedData = serialize($data);
        }
        catch (Throwable)
        {
            throw new InvalidArgumentException("Given data is not serializable !");
        }

        $filename = uniqid(time() . "-");

        $storage = self::getQueueStorage();
        $storage->write($filename, $serializedData);

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
        if ($storage->isEmpty())
            return;

        $logger = self::getQueueProcessingLogger();
        $capacity = self::getQueueProcessCapacity();

        $logger->info("Processing [".self::class."] queue items");

        # "Reserved" item are renamed, "#~" is put before their original name
        # A filename begining with "#~" must be ignored as it can be processed in another process
        # We can almost be sure that we are avoiding renaming collision by waiting a random period
        usleep(random_int(0, 1000));

        $count = 0;
        while ($count < $capacity)
        {

            $files = ObjectArray::fromArray($storage->listFiles())
            ->filter(fn($file) => !str_starts_with(basename($file), "#~"))
            ->collect();

            if (!($file = $files[0] ?? false))
                break;

            $newFileName = Utils::joinPath(dirname($file), "#~" . basename($file));
            rename($file, $newFileName);

            $data = unserialize(file_get_contents($newFileName));

            try
            {
                $count += self::processQueueItem($data) === true ? 1:0;
            }
            catch (Throwable $err)
            {
                $logger->info("Could not process queue item !", $data, $err);
            }
            finally
            {
                unlink($newFileName);
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

    /**
     * Process ONE item of the queue
     * - returning `true` means that the item was successfully processed
     * - returning `false` means that the item was skipped and that the class can handle another one instead
     */
    protected abstract static function processQueueItem(array $data): bool;
}