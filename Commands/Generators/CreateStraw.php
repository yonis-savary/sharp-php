<?php

namespace Sharp\Commands\Generators;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Core\Utils;

class CreateStraw extends Command
{
    protected function createStraw(string $name, string $app)
    {
        if (!preg_match("/^[A-Z][a-zA-Z]*$/", $name))
            return print("Given straw name must be in PascalCase\n");

        $directory = Utils::joinPath($app, "Classes/Straws");
        $file = Utils::joinPath($directory, $name. ".php");

        $namespace = Utils::pathToNamespace($directory);

        if (file_exists($file))
            return print("[$file] file already exists !\n");

        if (!is_dir($directory))
            mkdir($directory, recursive: true);

        file_put_contents($file, Terminal::stringToFile(
        "<?php

        namespace $namespace;

        class $name
        {
            use \Sharp\Classes\Extras\SessionStraw;
        }
        ", 2));

        return print("File created at [$file]\n");
    }

    public function __invoke(Args $args)
    {
        $values = $args->values();

        if (!count($values))
            $values = [Terminal::prompt("Straw name (PascalCase): ")];

        $app = Terminal::chooseApplication();

        foreach($values as $name)
            $this->createStraw($name, $app);
    }
}