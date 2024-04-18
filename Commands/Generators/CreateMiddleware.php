<?php

namespace Sharp\Commands\Generators;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Env\Storage;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Controller;
use Sharp\Classes\Web\MiddlewareInterface;
use Sharp\Core\Utils;

class CreateMiddleware extends Command
{
    use Controller;

    public function __invoke(Args $args)
    {
        $names = $args->values();
        if (!count($names))
            $names = [readline("Middleware name (PascalCase) > ")];

        $application = Terminal::chooseApplication();

        foreach ($names as $name)
        {
            if (!preg_match("/^[A-Z][\d\w]*$/", $name))
                return print("Name be must a PascalCase string\n");

            $middlewarePath = Utils::joinPath($application, "Middlewares");
            $storage = new Storage($middlewarePath);
            $filename = $name . ".php";

            if ($storage->isFile($name))
                return print($storage->path($filename) . " already exists !");

            $storage->write($filename, Terminal::stringToFile(
            "<?php

            namespace ".Utils::pathToNamespace($middlewarePath).";

            use ". MiddlewareInterface::class .";
            use ". Request::class .";
            use ". Response::class .";

            class $name implements MiddlewareInterface
            {
                public static function handle(Request \$request): Request|Response
                {
                    return \$request;
                }
            }
            "));

            echo "File written at ". $storage->path($filename) . "\n";
        }
    }

    public function getHelp(): string
    {
        return "Create middlewares(s) inside your application";
    }
}