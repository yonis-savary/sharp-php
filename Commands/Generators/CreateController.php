<?php

namespace Sharp\Commands\Generators;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Env\Storage;
use Sharp\Classes\Web\Controller;
use Sharp\Classes\Web\Router;
use Sharp\Core\Utils;

class CreateController extends Command
{
    use Controller;

    public function __invoke(Args $args)
    {
        $names = $args->values();
        if (!count($names))
            $names = [readline("Controller name (PascalCase) > ")];

        $application = Terminal::chooseApplication();

        foreach ($names as $name)
        {
            if (!preg_match("/^[A-Z][\d\w]*$/", $name))
                return print("Name be must a PascalCase string\n");

            $controllerPath = Utils::joinPath($application, "Controllers");
            $storage = new Storage($controllerPath);
            $filename = $name . ".php";

            if ($storage->isFile($name))
                return print($storage->path($filename) . " already exists !");

            $storage->write($filename, Terminal::stringToFile(
            "<?php

            namespace ".Utils::pathToNamespace($controllerPath).";

            use ". Controller::class .";
            use ". Router::class .";

            class $name
            {
                use Controller;

                public static function declareRoutes(Router \$router): void
                {
                    \$router->addGroup(
                        [],

                    );
                }
            }
            "));

            echo "File written at ". $storage->path($filename) . "\n";
        }
    }

    public function getHelp(): string
    {
        return "Create controller(s) inside your application";
    }
}