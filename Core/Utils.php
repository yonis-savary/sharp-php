<?php

namespace Sharp\Core;

use InvalidArgumentException;
use Sharp\Classes\Env\Config;

class Utils
{
    /*
        -------------------------------------
        Classes/Implementation Uils Functions
        -------------------------------------
    */

    /**
     * This function check if a class uses a specific trait
     *
     * @param mixed $class Class to check
     * @param string $trait Trait's "classname"
     * @return bool Do `$class` exists and uses `$trait` ?
     */
    public static function uses(mixed $objectOrClass, string $trait): bool
    {
        if (is_string($objectOrClass) && !class_exists($objectOrClass))
            return false;

        $traits = class_uses($objectOrClass);
        return $traits ? in_array($trait, array_keys($traits)): false;
    }

    /**
     * This function check if a class implements an interface
     *
     * @param string $class Class to check
     * @param string $interface Interface
     * @return bool Do `$class` exists and implements `$interface` ?
     */
    public static function implements(mixed $objectOrClass, string $interface): bool
    {
        if (is_string($objectOrClass) && !class_exists($objectOrClass))
            return false;

        $implements = class_implements($objectOrClass);
        return $implements ? in_array($interface, $implements) : false;
    }

    /**
     * This function check if a class extends from another
     *
     * @param string $class Class to check
     * @param string $parent Parent class
     * @return bool Do `$class` exists and extends from `$parent` ?
     */
    public static function extends(mixed $objectOrClass, string $parent): bool
    {
        if (is_string($objectOrClass) && !class_exists($objectOrClass))
            return false;

        $parents = class_parents($objectOrClass);
        return $parents ? in_array($parent, $parents) : false;
    }

    /*
        --------------------
        Path Utils Functions
        --------------------
    */

    /**
     * Given a path, this function make sure the path is valid
     * and use "/" as a directory separator
     */
    public static function normalizePath(string $path): string
    {
        return preg_replace("/\/{2,}/", "/", str_replace("\\", "/", $path));
    }

    /**
     * @return string Normalized path from given path/file parts
     */
    public static function joinPath(...$parts): string
    {
        return self::normalizePath(join("/", $parts));
    }

    /**
     * Make an absolute path from given path/file parts
     * @note Given path can be absolute, it will not be altered
     */
    public static function relativePath(...$parts): string
    {
        $relative = self::joinPath(...$parts);
        if (str_contains($relative, Autoloader::projectRoot()))
            return $relative;

        return self::joinPath(Autoloader::projectRoot(), $relative);
    }

    /**
     * Attempt to transform a file path into a clear relative namespace
     * to the project root
     */
    public static function pathToNamespace(string $path): string
    {
        $path = str_replace(Autoloader::projectRoot(), "", $path);
        $namespace = str_replace("/", "\\", $path);
        $namespace = preg_replace("/^\\\\|\..+$/", "", $namespace);

        return $namespace;
    }

    /**
     * Attempt to transform a classname (namespace + classname) into
     * a file path relative to the project root
     */
    public static function classnameToPath(string $classname): string
    {
        return Utils::relativePath(str_replace("\\", "/", $classname).".php");
    }

    /*
        -----------------------------
        Directory/File Utils function
        -----------------------------
    */

    const NO_FILTER = 0;
    const ONLY_DIRS = 1;
    const ONLY_FILES = 2;

    /**
     * Explore a directory and return a list of absolutes Directory/Files paths
     * A filter can be used to edit the results:
     * - `Utils::ONLY_DIRS` : Only return names of (sub)directories
     * - `Utils::ONLY_FILES` : Only return names of (sub)files
     */
    public static function exploreDirectory(string $path, int $mode=self::NO_FILTER): array
    {
        $results = [];
        foreach (array_slice(scandir($path), 2) as $file)
        {
            $fullpath = Utils::joinPath($path, $file);

            if (is_dir($fullpath))
            {
                if ($mode !== self::ONLY_FILES)
                    $results[] = $fullpath;

                array_push($results, ...self::exploreDirectory($fullpath, $mode));
            }
            else if ($mode !== self::ONLY_DIRS)
            {
                $results[] = $fullpath;
            }
        }
        return $results;
    }

    /**
     * @param string $directory Directory to scan
     * @return array Absolute path from direct FILES
     */
    public static function listFiles(string $directory): array
    {
        $allFiles = array_slice(scandir($directory), 2);
        $allFiles = array_map(fn($x) => Utils::joinPath($directory, $x), $allFiles);
        $onlyFiles = array_filter($allFiles, "is_file");
        return array_values($onlyFiles);
    }

    /**
     * @param string $directory Directory to scan
     * @return array Absolute path from direct DIRECTORIES
     */
    public static function listDirectories(string $directory): array
    {
        $allFiles = array_slice(scandir($directory), 2);
        $allFiles = array_map(fn($x) => Utils::joinPath($directory, $x), $allFiles);
        $onlyDirectories = array_filter($allFiles, "is_dir");
        return array_values($onlyDirectories);
    }

    /*
        ------------------------------
        Byte/Transform utils functions
        ------------------------------
    */

    /**
     * Do a bitwise AND to check if `$needle` is present in `$haystack`
     *
     * @param int $haystack Full value that may contain the flag
     * @param int $needle Researched value in `$haystack`
     * @example FALSE `valueHasFlag(0b1010, 0b0100) // <= false`
     * @example TRUE `valueHasFlag(0b1010, 0b1000) // <= true`
     */
    public static function valueHasFlag(int $haystack, int $needle): bool
    {
        return ($haystack & $needle) === $needle;
    }

    /**
     * @return bool Is given array associative ?
     * @note an empty array will be considered as a list (non-assoc)
     */
    public static function isAssoc(array $array)
    {
        if (!count($array))
            return false;
        return !array_is_list($array);
    }

    /**
     * Make sure a value is contained inside an array
     * (Can detect associative arrays)
     *
     * @example number `Utils::toArray(5) === [5]`
     * @example existant_array `Utils::toArray(['A']) === ['A']`
     * @example assoc `Utils::toArray(['key'=>'value']) === [['key'=>'value']]`
     */
    public static function toArray(mixed $value)
    {
        if (!is_array($value))
            return [$value];

        if (self::isAssoc($value))
            return [$value];

        return $value;
    }


    /**
     * Given an associative array, this function return a lowercase-keys version of the same array
     */
    public static function lowerArrayKeys(array $array): array
    {
        $newArray = [];

        foreach ($array as $key => $value)
        {
            if (array_key_exists($key, $newArray))
                throw new InvalidArgumentException("Duplicate key [$key]");

            $newArray[strtolower($key)] = $value;
        }
        return $newArray;
    }


    /**
     * Useful to enable debug-only features
     *
     * @return `true` if "env" is set to "production" in your configuration
     */
    public static function isProduction(): bool
    {
        $env = Config::getInstance()->get("env", "debug");
        return strtolower($env) === "production";
    }
}