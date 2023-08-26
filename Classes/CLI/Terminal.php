<?php

namespace Sharp\Classes\CLI;

use Sharp\Classes\Env\Config;

class Terminal
{
    /**
     * Simply an alias to `readline()`
     */
    public static function prompt(string $question)
    {
        return readline($question);
    }

    /**
     * Display a list to the user and ask to choose an item
     * @param array $choices Choices for the user
     * @param string $question Prompt for the user
     * @return mixed Selected option (the value, not index)
     */
    public static function promptList(array $choices, string $question)
    {
        echo "$question\n";
        for ($i=0; $i<count($choices); $i++)
            echo " " . ($i+1) ." - $choices[$i]\n";
        echo "\n";

        $index = intval(self::prompt("> "));
        return $choices[$index - 1] ?? null;
    }

    /**
     * Make the user choose between enabled applications
     * @note If only one application is enabled, it is choosed by default
     * @return string Choosen App relative path (as written in configuration)
     */
    public static function chooseApplication()
    {
        $applications = Config::getInstance()->get("applications");

        if (!is_array($applications))
            return $applications;

        if (count($applications) === 1)
            return $applications[0];

        return self::promptList(
            $applications,
            "This command needs you to select an appliction"
        );
    }

    /**
     * Util function to write a string and remove excessives tabs before lines
     */
    public static function stringToFile(string $content, int $d=3)
    {
        return preg_replace('/^( {4}){'.$d."}|(^ +\n?$)/m", '', $content);
    }
}