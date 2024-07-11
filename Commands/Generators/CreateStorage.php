<?php 

namespace Sharp\Commands\Generators;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;

class CreateStorage extends Command
{
    public function __invoke(Args $args)
    {
        $application = Terminal::chooseApplication();

        $name = $args->values()[0] ?? readline("Storage Name ?");
        if (!preg_match("/^([A-Z][a-zA-Z0-9]*)+$/", $name))
            return print("Given name must be made of PascalName words\n");
        $filename = $name . ".php";

        $storagePath = new Storage(Utils::joinPath($application, "Classes/App/Storages"));
        if ($storagePath->isFile($filename))
            return print("$filename already exists !\n");

        $storagePath->write($filename, Terminal::stringToFile(
            "<?php

            namespace $application\\Classes\\App\\Storages;

            use Sharp\Classes\Utils\AppStorage;

            class $name 
            {
                use AppStorage;
            } 
        "));

        echo "File written at : " . $storagePath->path($filename) . "\n";
    }
}