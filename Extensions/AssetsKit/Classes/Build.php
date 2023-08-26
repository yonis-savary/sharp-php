<?php

namespace Sharp\Extensions\AssetsKit\Classes;

use Sharp\Classes\CLI\AbstractBuildTask;
use Sharp\Core\Utils;

class Build extends AbstractBuildTask
{
    public function execute()
    {
        $assetsKitDir = Utils::relativePath('Sharp/Extensions/AssetsKit');

        $this->log("Building stylesheet...\n");
        $styleDir = Utils::joinPath($assetsKitDir, '/Assets/less');
        $this->shellInDirectory('lessc main.less ../css/assets-kit/style.css --verbose', $styleDir, true);
    }
}