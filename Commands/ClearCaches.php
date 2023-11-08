<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Classes\CacheElement;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;

class ClearCaches extends Command
{
    public function getHelp(): string
    {
        return "Delete files in Storage/Cache, use --all to delete permanent items";
    }

    protected function processFile(string $file, bool $deletePermanent)
    {
        if (!($cacheElement = CacheElement::fromFile($file)))
        {
            $this->log("Deleting $file");
            unlink($file);
            return;
        }

        $isPermanent = ($cacheElement->getTimeToLive() === Cache::PERMANENT);

        if ($isPermanent && (!$deletePermanent))
            return $this->log("Ignoring $file");

        $this->log("Deleting $file");
        unlink($file);
    }

    public function __invoke(Args $args)
    {
        $cache = Storage::getInstance()->getSubStorage("Cache");
        $deletePermanent = $args->isPresent("a", "all");

        foreach ($cache->exploreDirectory("/", Utils::ONLY_FILES) as $file)
            $this->processFile($file, $deletePermanent);
    }
}