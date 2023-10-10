<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;

class Serve extends Command
{
    public function getHelp(): string
    {
        return "Start built-in PHP server in Public, default port is 8000 (ex: php do serve 5000)";
    }

    public function __invoke(Args $args)
    {
        $port = intval($args->values()[0] ?? 8000);

        echo join("\n", [
            "",
            "Serving on port $port (http://localhost:$port)...\n",
            ""
        ]);

        chdir("Public");
        $proc = popen("php -S localhost:$port", "r");

        while (!feof($proc))
            echo fread($proc, 1024);
    }
}