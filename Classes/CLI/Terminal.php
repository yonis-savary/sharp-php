<?php

namespace Sharp\Classes\CLI;

use Sharp\Classes\Env\Configuration;

class Terminal
{
    /**
     * Simply an alias to `readline()`
     */
    public static function prompt(string $question): string
    {
        return readline($question);
    }

    public static function confirm(string $question): bool
    {
        $str = readline($question . " (y/n) : ");
        $str = strtoupper($str);

        return $str === "Y";
    }

    /**
     * Display a list to the user and ask to choose an item
     * @param array $choices Choices for the user
     * @param string $question Prompt for the user
     * @return mixed Selected option (the value, not index)
     */
    public static function promptList(array $choices, string $question): mixed
    {
        print("$question\n");
        for ($i=0; $i<count($choices); $i++)
            printf(" %s - %s\n", $i+1, $choices[$i]);

        $index = intval(self::prompt("\n> "));
        return $choices[$index-1] ?? null;
    }

    /**
     * Make the user choose between enabled applications
     * @note If only one application is enabled, it is chosen by default
     * @return string Chosen App relative path (as written in configuration)
     */
    public static function chooseApplication(): string
    {
        $applications = Configuration::getInstance()->toArray("applications");

        if (count($applications) === 1)
            return $applications[0];

        return self::promptList(
            $applications,
            "This command needs you to select an application"
        );
    }

    /**
     * Util function to write a string and remove excessive tabs before lines
     */
    public static function stringToFile(string $content, int $d=3): string
    {
        return preg_replace('/^( {4}){'.$d."}|(^ +\n?$)/m", '', $content);
    }
}