<?php

namespace Sharp\Extensions\AssetsKit\Components;

use Exception;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Env\Cache;
use Sharp\Core\Autoloader;
use Sharp\Core\Utils;

/**
 * This class is the implementation of SVGInterface,
 * it can also be configured with these keys:
 * - enabled : is the component supposed to serve its purpose ?
 * - path : relative path from your project directory to your svg collection
 * - cached : should requested icons be cached ?
 */
class Svg
{
    use Component,Configurable;

    const CACHE_KEY = 'components.svg.cache';
    protected $cache = [];
    protected string $cacheHash ="";

    public static function getDefaultConfiguration(): array
    {
        return [
            'enabled' => true,
            'path' => 'Sharp/Extensions/AssetsKit/vendor/twbs/bootstrap-icons/icons',
            'cached' => true,
            'default_size' => 24,
            'max-age' => 3600*24*7, // 1 Weeks
            "middlewares" => []
        ];
    }

    public static function getKeyToLoad(): string
    {
        return 'svg';
    }

    public static function initialize()
    {
        $instance = self::getInstance();
        if ($instance->isEnabled())
            $instance->handleRequest( Request::buildFromGlobals() );

    }

    public function __construct()
    {
        $this->loadConfiguration();

        $path = $this->configuration['path'];

        if (!str_ends_with($path, '/'))
            $path = "$path/";

        if (str_starts_with($path, '/'))
            $path = substr($path, 1);

        $this->configuration['path'] = $path;

        // Code here will be executed the first time SVG component will be called
        $this->cache = Cache::getInstance()->get(self::CACHE_KEY, []);
        $this->cacheHash = md5(print_r($this->cache, true));
    }

    public function handleRequest(Request $req, bool $returnResponse=false)
    {
        $route = Route::get("/assets/svg", [$this, "serve"], $this->configuration["middlewares"]);

        if (!$route->match($req))
            return;

        $response = $route($req);

        if ($age = $this->configuration["max-age"])
            $response->withHeaders(["Cache-Control" => "max-age=$age"]);

        if ($returnResponse)
            return $response;

        $response->display();
        die;
    }

    // ---------- internal methods ----------

    protected function getCollectionPath(): string
    {
        return Utils::relativePath($this->configuration['path']);
    }

    protected function getIconPath(string $name): string
    {
        return Utils::joinPath($this->getCollectionPath(), $name . '.svg');
    }

    protected function loadSVGFromName(string $name)
    {
        $path = $this->getIconPath($name);

        Logger::getInstance()->debug($path);
        if (!file_exists($path))
            throw new Exception('File not found !');

        $content = file_get_contents($path);

        if ($this->configuration['cached'] === true)
            $this->cache[$name] = $content;

        return $content;
    }

    public function __destruct()
    {
        $cacheHash = md5(print_r($this->cache, true));
        if ($this->cacheHash === $cacheHash)
            return;

        if ($this->configuration['cached'] === true)
            Cache::getInstance()->set(self::CACHE_KEY, $this->cache, 3600*24);
    }

    // ---------- implementation ----------


    public function get(string $name, int $size=null): string|null
    {
        $content = $this->cache[$name] ?? $this->loadSVGFromName($name);

        if ($size !== null)
            $content = preg_replace('/(height|width)=".*?"/', "$1='$size'", $content);

        return $content;
    }

    public function exists(string $name): bool
    {
        return file_exists($this->getIconPath($name));
    }

    public function serve(Request $req): Response
    {
        self::loadConfiguration();

        if ($this->configuration['enabled'] !== true)
            return Response::json('Disabled component', 400);

        list($name, $size) = $req->list('name', 'size');
        if ($name === null)
            return Response::json('\'name\' parameter is needed !', 400);

        $content = Svg::get($name, $size);

        $res = Response::html($content);
        $res->withHeaders([
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'max-age='.(3600*24),
        ]);

        header_remove('Pragma');
        return $res;
    }
}
