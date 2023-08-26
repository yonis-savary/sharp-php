<?php

namespace Sharp\Classes\Web;

use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Env\Cache;
use Sharp\Core\Autoloader;
use Sharp\Core\Utils;
use Throwable;

/**
 * Given a set of `Routes`, this component is able to
 * route a `Request` and execute the matched route
 */
class Router
{
    use Component, Configurable;

    /** @var array (`NULL` is NOT supported as it can represent an absence of function response !) */
    const INTERPRETED_TYPES = ['boolean', 'integer', 'double', 'string', 'array', 'object'];

    protected array $group = [];
    protected array $routes = [];

    protected ?Route $cachedRoute = null;


    public function __construct()
    {
        $this->loadConfiguration();
    }

    public static function getDefaultConfiguration(): array
    {
        return [
            "cached" => false
        ];
    }




    protected function getCacheKey( Request $request ): string
    {
        $hash = md5($request->getPath());
        return "sharp-router-index-$hash";
    }

    protected function putRouteToCache(Route $route, Request $request)
    {
        if (!is_array($route->getCallback()))
            return;

        $key = $this->getCacheKey($request);
        $cache = Cache::getInstance();
        $cache->set($key, $route);
    }

    protected function getRoutesFromCache(Request $request): bool
    {
        $cache = Cache::getInstance();
        if (!($this->cachedRoute = $cache->get($this->getCacheKey($request), null)))
            return false;

        return true;
    }

    /**
     * Try to load routes from the cache, on failure, load routes from files/controllers
     */
    public function loadRoutesOrCache(Request $request=null)
    {
        if ($request && $this->configuration["cached"])
        {
            if ($this->getRoutesFromCache($request))
                return debug("USING CACHE !");
        }

        $this->loadAutoloaderFiles();
        $this->loadControllersRoutes();
    }

    protected function loadAutoloaderFiles()
    {
        foreach (Autoloader::getListFiles(Autoloader::ROUTES) as $file)
            require_once $file;
    }

    protected function loadControllersRoutes()
    {
        foreach (Autoloader::getListFiles(Autoloader::AUTOLOAD) as $file)
        {
            if (!str_contains($file, "Controllers"))
                continue;

            $class = Utils::pathToNamespace($file);
            if (!class_exists($class))
                continue;

            if (!Utils::uses($class, "Sharp\Classes\Web\Controller"))
                continue;

            $class::declareRoutes();
        }
    }


    public function group(
        array $group,
        callable $callback
    ) {
        $original = $this->group;

        foreach ($group as $key => $value)
            $this->group[$key] = array_merge($this->group[$key] ?? [], Utils::toArray($value));

        $callback($this);

        $this->group = $original;
    }

    public function addRoutes(Route ...$routes)
    {
        foreach ($routes as $route)
        {
            $this->applyGroupsTo($route);

            $path = $route->getPath();
            if (!str_starts_with($path, "/"))
            {
                $path = "/$path";
                $route->setPath($path);
            }

            $this->routes[] = $route;
        }
    }

    protected function applyGroupsTo(Route &$route)
    {
        if ($this->group["path"] ?? false)
        {
            $prefix = join("/", $this->group["path"]);
            $route->setPath(str_replace("//", "/", $prefix . $route->getPath()));
        }

        if ($this->group["middlewares"] ?? false)
        {
            foreach ($this->group["middlewares"] as $middleware)
                $route->addMiddlewares($middleware);
        }
    }

    protected function findFirstMathingRoute(Request $req) : ?Route
    {
        foreach ($this->routes as $route)
        {
            /** @var Route $route */
            if (!$route->match($req))
                continue;

            if ($this->configuration["cached"])
                $this->putRouteToCache($route, $req);

            return $route;
        }
        return null;
    }

    public function route(Request $request): Response
    {
        $route = $this->cachedRoute ?? $this->findFirstMathingRoute($request);

        if (!$route)
            return new Response("Page not found", 404, ["Content-Type" => "text/plain"]);

        try
        {
            $response = $route($request);

            if (!($response instanceof Response))
            {
                if (!in_array(gettype($response), self::INTERPRETED_TYPES))
                    return new Response(null, 204);

                $response = Response::json($response);
            }

            return $response;
        }
        catch (Throwable $err)
        {
            Logger::getInstance()->error($err->getMessage());
            return new Response("Internal server error", 500, ["Content-Type" => "text/plain"]);
        }
    }

    /**
     * @note TEST-PURPOSE-METHOD
     */
    public function deleteRoutes()
    {
        $this->routes = [];
    }

    /**
     * @return array<Route>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}