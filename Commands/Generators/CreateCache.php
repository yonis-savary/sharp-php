<?php 

namespace Sharp\Commands\Generators;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;

class CreateCache extends Command
{
    public function __invoke(Args $args)
    {
        $application = Terminal::chooseApplication();

        $name = $args->values()[0] ?? readline("Storage Name ?");
        if (!preg_match("/^([A-Z][a-zA-Z0-9]*)+$/", $name))
            return print("Given name must be made of PascalName words\n");
        $filename = $name . ".php";

        $cacheStorage = new Storage(Utils::joinPath($application, "Classes/App/Caches"));
        if ($cacheStorage->isFile($filename))
            return print("$filename already exists !\n");

        $cacheStorage->write($filename, Terminal::stringToFile(
            "<?php

            namespace $application\\Classes\\App\\Caches;

            use Sharp\Classes\Utils\AppCache;

            class $name 
            {
                use AppCache;
            } 
        "));

        echo "File written at : " . $cacheStorage->path($filename) . "\n";
    }
}