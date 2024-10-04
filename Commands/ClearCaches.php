<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Classes\CacheElement;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Autoloader;
use Sharp\Core\Utils;

class ClearCaches extends Command
{
    public function getHelp(): string
    {
        return "Delete files in Storage/Cache, use --all to delete permanent items";
    }

    protected function processFile(string $file, bool $deletePermanent)
    {
        $relPath = str_replace(Autoloader::projectRoot(), "", $file);
        if (str_starts_with($relPath, "/"))
            $relPath = substr($relPath, 1);

        if (!($cacheElement = CacheElement::fromFile($file)))
        {
            $this->log("Deleting non-cache item file $relPath");
            unlink($file);
            return;
        }

        $isPermanent = ($cacheElement->getTimeToLive() === Cache::PERMANENT);

        if ($isPermanent && (!$deletePermanent))
            return $this->log("Ignoring permanent item $file");


        $this->log("Deleting cache item $relPath");
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