<?php

namespace Sharp\Commands\Configuration;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;

class BackupConfiguration extends Command
{
    public function __invoke(Args $args)
    {
        $currentConfig = Utils::relativePath("sharp.json");

        if (!is_file($currentConfig))
            return print("No configuration to backup");

        $copyBasename =
            "sharp-json-".
            substr(md5_file($currentConfig), 0, 4).
            "-".
            time().
            ".json";

        $copyPath = Storage::getInstance()->path($copyBasename);

        copy($currentConfig, $copyPath);
        echo "Configuration backup written to ./Storage/$copyBasename\n";
    }
}