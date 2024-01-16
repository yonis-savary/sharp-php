<?php

namespace Sharp\Core;

use InvalidArgumentException;
use RuntimeException;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Env\Configuration;
use Sharp\Classes\Events\FailedAutoload;
use Sharp\Classes\Events\LoadedFramework;
use Throwable;

class Autoloader
{
    /** Files in this list are available to the autoloader but not directly required */
    const AUTOLOAD = "autoload";

    /** Files in this list are considered as Views/Templates */
    const VIEWS = "views";

    /** Files in this list are not PHP file but resources like JS, CSS... */
    const ASSETS = "assets";

    /** Files in this list are directly required by the autoloader */
    const REQUIRE = "require";

    /** Files in this list are not directly required by the autoloader but can be by other classes (like `Router`) */
    const ROUTES = "routes";

    /**
     * This constant old the different purpose of
     * an application directory
     */
    const DIRECTORIES_PURPOSE = [
        "Assets"      => self::ASSETS,
        "Commands"    => self::AUTOLOAD,
        "Controllers" => self::AUTOLOAD,
        "Components"  => self::AUTOLOAD,
        "Classes"     => self::AUTOLOAD,
        "Models"      => self::AUTOLOAD,
        "Routes"      => self::ROUTES,
        "Views"       => self::VIEWS,
        "Helpers"     => self::REQUIRE,
    ];

    const CACHE_FILE = "Storage/Cache/autoload.php.cache";

    /**
     * Hold the absolute path to the project root
     * (Nor Public or Sharp directory, but the parent)
     */
    protected static ?string $projectRoot = null;

    /**
     * This variable holds directories path with
     * `Purpose => [...directories]`
     */
    protected static array $lists = [];

    /** Used to cache the results of `getClassesList()` */
    protected static array $cachedClassList = [];

    /** Used to cache the results of `getListFiles()` */
    protected static array $listsCache = [];

    public static function initialize()
    {
        self::findProjectRoot();
        self::registerAutoloadCallback();

        if (!self::loadAutoloadCache())
            self::loadApplications();

        EventListener::getInstance()->dispatch(new LoadedFramework());
    }

    /**
     * Try to find the project root directory, crash on failure
     */
    protected static function findProjectRoot()
    {
        if (self::$projectRoot)
            return self::$projectRoot;

        $original = getcwd();

        try
        {
            while (!is_dir("./Sharp"))
                chdir("..");

            self::$projectRoot = Utils::normalizePath(getcwd());
        }
        catch (Throwable)
        {
            throw new RuntimeException("Cannot find Sharp project root directory !");
        }

        chdir($original);
    }

    public static function projectRoot(): string
    {
        return self::$projectRoot;
    }

    public static function registerAutoloadCallback()
    {
        spl_autoload_register(function($class){
            $file = Utils::classnameToPath($class);

            if (is_file($file))
                require_once $file;
            else
                EventListener::getInstance()->dispatch(new FailedAutoload($class, $file));
        });
    }

    protected static function loadApplications()
    {
        $config = Configuration::getInstance();
        $applications = $config->toArray("applications", []);

        // The framework is loaded as an application
        array_unshift($applications, "Sharp");

        foreach ($applications as $app)
            self::loadApplication($app, false);

        foreach (self::getListFiles(self::REQUIRE) as $file)
            require_once $file;
    }

    public static function loadApplication(string $path, bool $requireHelpers=true)
    {
        $application = Utils::relativePath($path);

        if (!is_dir($application))
            throw new InvalidArgumentException("[$application] is not a directory !");

        $vendorFile = Utils::joinPath($application, "vendor/autoload.php");
        if (is_file($vendorFile))
            require_once $vendorFile;

        foreach (Utils::listDirectories($application) as $directory)
        {
            $basename = basename($directory);

            if (!$purpose = self::DIRECTORIES_PURPOSE[$basename] ?? false)
                continue;

            if ($purpose === self::REQUIRE && $requireHelpers)
            {
                foreach (Utils::exploreDirectory($directory, Utils::ONLY_FILES) as $toRequire)
                    require_once $toRequire;
            }

            self::addToList($purpose, $directory);
        }
    }

    public static function addToList(string $list, ...$elements): void
    {
        self::$lists[$list] ??= [];
        array_push(self::$lists[$list], ...$elements);
    }

    public static function getList(string $name): array
    {
        return self::$lists[$name] ?? [];
    }

    public static function getListFiles(string $name): array
    {
        if ($cachedResult = self::$listsCache[$name] ?? false)
            return $cachedResult;

        $results = [];

        foreach (self::getList($name) as $directory)
            array_push($results, ...Utils::exploreDirectory($directory, Utils::ONLY_FILES));

        return $results;
    }

    /**
     * @param bool $forceReload By default, the result is cached but you can force the function to refresh it
     * @return array Classes list of your applications (Controllers, Models, Components, Classes...etc)
     */
    public static function getClassesList(bool $forceReload=false): array
    {
        if (self::$cachedClassList && !$forceReload)
            return self::$cachedClassList;

        $files = self::getListFiles(self::AUTOLOAD);

        self::$cachedClassList =
            ObjectArray::fromArray($files)
            ->map(fn($x) => Utils::pathToNamespace($x))
            ->filter(class_exists(...))
            ->collect();

        return self::$cachedClassList;
    }

    /**
     * Filter every applications classes by passing them to a callback
     * @param callable $filter Filter callback, return `true` to accept a class, `false` to ignore it
     * @return array Filtered class list
     */
    public static function filterClasses(callable $filter): array
    {
        return ObjectArray::fromArray(self::getClassesList())
        ->filter($filter)
        ->collect();
    }

    /**
     * @return array List of classes that implements `$interface` interface
     */
    public static function classesThatImplements(string $interface): array
    {
        return self::filterClasses(fn($e)=> Utils::implements($e, $interface));
    }

    /**
     * @return array List of classes that extends `$class` parent class
     */
    public static function classesThatExtends(string $class): array
    {
        return self::filterClasses(fn($e)=> Utils::extends($e, $class));
    }

    /**
     * @return array List of classes that use the `$trait` Trait
     */
    public static function classesThatUses(string $trait): array
    {
        return self::filterClasses(fn($e)=> Utils::uses($e, $trait));
    }

    public static function loadAutoloadCache(): bool
    {
        if (!is_file(self::CACHE_FILE))
            return false;

        list(
            self::$lists,
            self::$listsCache,
            self::$cachedClassList
        ) = include(self::CACHE_FILE);

        return true;
    }

    public static function writeAutoloadCache()
    {
        $toString = function($var) {
            if (Utils::isAssoc($var))
            {
                foreach ($var as $key => &$value)
                    $value = "'$key' => ['".join("','", $value)."']";
            }
            else
            {
                $var = array_map(fn($e) => "'$e'", $var);
            }

            return "[".join(",", array_values($var))."]";
        };

        // Calling `getListFiles` to cache every possible results
        foreach (array_keys(self::$lists) as $key)
            self::getListFiles($key);

        file_put_contents(self::CACHE_FILE, Terminal::stringToFile(
        "<?php

        return [".join(",", [
            $toString(self::$lists),
            $toString(self::$listsCache),
            $toString(self::$cachedClassList),
        ])."];", 2));
    }
}