<?php 

namespace Sharp\Commands\Generators;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;

class CreateMap extends Command
{
    public function __invoke(Args $args)
    {
        $application = Terminal::chooseApplication();

        $name = $args->values()[0] ?? readline("Map Name ?");
        if (!preg_match("/^([A-Z][a-zA-Z0-9]*)+$/", $name))
            return print("Given name must be made of PascalName words\n");
        $filename = $name . ".php";

        $mapsStorage = new Storage(Utils::joinPath($application, "Classes/App/Maps"));
        if ($mapsStorage->isFile($filename))
            return print("$filename already exists !\n");

        $mapsStorage->write($filename, Terminal::stringToFile(
            "<?php

            namespace $application\\Classes\\App\\Maps;

            use Sharp\Classes\Utils\AppMap;

            class $name 
            {
                use AppMap;
            } 
        "));

        echo "File written at : " . $mapsStorage->path($filename) . "\n";
    }
}