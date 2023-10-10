<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Configuration;
use Sharp\Core\Utils;

class Test extends Command
{
    public function getHelp(): string
    {
        return "Execute every PHPUnit installation/test suites";
    }

    protected function executeInDir(callable $callback, string $directory)
    {
        $original = getcwd();
        chdir($directory);
        $callback();
        chdir($original);
    }

    public function __invoke(Args $args)
    {
        $toTest = Configuration::getInstance()->toArray("applications");

        // The framework need to be tested too
        array_unshift($toTest, "Sharp");

        foreach ($toTest as $application)
        {
            $phpunit = Utils::joinPath($application, "vendor/bin/phpunit");
            if (!is_file($phpunit))
                continue;

            $this->executeInDir(function() use ($application) {

                $start = hrtime(true);
                $output = shell_exec("./vendor/bin/phpunit --colors=never --display-warnings");
                $duration = hrtime(true) - $start;

                $durationMicrosecond = $duration/1_000_000;

                $lines = array_filter(explode("\n", $output));

                $lastLine = end($lines);

                if (str_starts_with($lastLine, "OK"))
                    echo " - OK ($application, " . substr($lastLine, 4) ." in $durationMicrosecond µs\n";
                else
                    echo "Errors/Warnings while testing [$application] :\n$output";

            }, $application);
        }
    }
}