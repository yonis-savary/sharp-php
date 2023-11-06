<?php

namespace Sharp\Classes\CLI;

use Sharp\Classes\Core\Logger;

/**
 * Command classes can be executed through the CLI,
 * the only method you have to override is `__invoke()` and `getHelp()`
 */
abstract class Command
{
    final public function log(string ...$mixed)
    {
        if (php_sapi_name() === "cli")
            return print(join("", array_map(fn($x) => $x . "\n", $mixed)));

        return Logger::getInstance()->info(...$mixed);
    }

    final public function getOrigin(): string
    {
        $class = get_called_class();
        $origin = preg_replace("/(\\\\Commands)?\\\\[^\\\\]+$/", "", $class);
        $origin = preg_replace("/.+\\\\/", "", $origin);
        $origin = preg_replace("/([a-z])([A-Z])/", "$1-$2", $origin);
        $origin = strtolower($origin);
        return $origin;
    }

    final public function getIdentifier(): string
    {
        return $this->getOrigin() . "@" . $this->getName();
    }

    final public function getName(): string
    {
        $class = get_called_class();
        $class = preg_replace("/.+\\\\/", "", $class);
        $class = preg_replace("/([a-z])([A-Z])/", "$1-$2", $class);
        $class = strtolower($class);
        return $class;
    }

    public function getHelp(): string
    {
        return "";
    }

    /**
     * This function is executed when the command is called
     */
    public abstract function __invoke(Args $args);
}