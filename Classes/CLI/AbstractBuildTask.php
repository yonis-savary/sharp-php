<?php

namespace Sharp\Classes\CLI;

abstract class AbstractBuildTask extends CLIUtils
{
    /**
     * Main function of your build task, called every build
     */
    public abstract function execute();
}