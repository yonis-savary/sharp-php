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

    protected array $group = [];

    /** @var array<Route> $routes Set of registered routes */
    protected array $routes = [];

    protected ?Route $cachedRoute = null;
    protected ?Cache $cache = null;

    public function __construct(Cache $cache=null)
    {
        $this->cache = $cache ?? Cache::getInstance();
        $this->getConfiguration();
    }

    public static function getDefaultConfiguration(): array
    {
        return [
            "cached" => false
        ];
    }

    protected function getCacheKey(Request $request): string
    {
        $hash = md5($request->getPath());
        return "sharp-router-index-$hash";
    }

    protected function putRouteToCache(Route $route, Request $request): void
    {
        if (!is_array($route->getCallback()))
            return;

        $key = $this->getCacheKey($request);
        $this->cache->set($key, $route);
    }

    protected function getRoutesFromCache(Request $request): bool
    {
        $key = $this->getCacheKey($request);
        if ($this->cachedRoute = $this->cache->get($key, null))
            return true;

        return false;
    }

    /**
     * Try to load routes from the cache, on failure, load routes from files/controllers
     */
    public function loadRoutesOrCache(Request $request=null)
    {
        if ($request && $this->configuration["cached"])
        {
            if ($this->getRoutesFromCache($request))
                return;
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
            $this->routes[] = $route;
        }
    }

    protected function applyGroupsTo(Route &$route)
    {
        if (count($this->group["path"] ?? []))
        {
            $prefix = "/" . join("/", $this->group["path"]);
            $route->setPath(str_replace("//", "/", $prefix . $route->getPath()));
        }

        if (count($this->group["middlewares"] ?? []))
        {
            foreach ($this->group["middlewares"] as $middleware)
                $route->addMiddlewares($middleware);
        }
    }

    protected function findFirstMathingRoute(Request $req) : ?Route
    {
        foreach ($this->routes as $route)
        {
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
            return Response::adapt($route($request));
        }
        catch (Throwable $err)
        {
            Logger::getInstance()->logThrowable($err);
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