<?php

namespace Sharp\Commands\Troubleshoot\Checkers;

use Sharp\Commands\Troubleshoot\Contract\AbstractCodeChecker;
use Sharp\Core\Utils;

class NamespaceChecker extends AbstractCodeChecker
{
    public function getSuccessMessage(): string
    {
        return "All namespaces found are valid";
    }

    public function getErrorMessage(): string
    {
        return "Found invalid namespaces";
    }

    public function getPurposeMessage(): string
    {
        return "Checking classes namespaces";
    }

    public function checkClassFile(string $file): string|bool
    {
        $content = file_get_contents($file);
        $matches = [];

        if (!preg_match("/namespace (.+);/", $content, $matches))
            return "Don't found any namespace in [$file]";

        $expectedNamespace = Utils::pathToNamespace(dirname($file));
        $actualNamespace = $matches[1];

        if ($expectedNamespace === $actualNamespace)
            return true;

        return
            "[./$file] don't have expected namespace\n".
            "   - Expected: $expectedNamespace\n".
            "   + Actual  : $actualNamespace\n"
        ;
    }
}