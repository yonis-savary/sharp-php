<?php

namespace Sharp\Classes\Web;

use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Events\RoutedRequest;
use Sharp\Classes\Events\RouteNotFound;
use Sharp\Core\Autoloader;
use Sharp\Core\Utils;
use Sharp\Classes\Web\Controller;

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
    protected bool $loadedRoutes = false;


    public function __construct(Cache $cache=null)
    {
        $this->loadConfiguration();
        $this->cache = $cache ?? Cache::getInstance()->getSubCache("router");
    }

    public static function getDefaultConfiguration(): array
    {
        return ["cached" => false];
    }

    protected function getCacheKey(Request $request): string
    {
        return md5($request->getMethod() . $request->getPath());
    }

    protected function putRouteToCache(Route $route, Request $request): void
    {
        if (!is_array($route->getCallback()))
            return;

        foreach ($route->getMiddlewares() as $middlewares)
        {
            if (!is_array($middlewares))
                return;
        }

        $this->cache->set(
            $this->getCacheKey($request),
            $route
        );
    }

    protected function getCachedRouteForRequest(Request $request): ?Route
    {
        if (!$this->isCached())
            return null;

        $key = $this->getCacheKey($request);
        if ($cachedRoute = $this->cache->try($key))
        {
            // useful to register slug values for cached routes
            $cachedRoute->match($request);
            return $cachedRoute;
        }

        return null;
    }

    /**
     * Try to load routes from the cache, on failure, load routes from files/controllers
     * @param bool $force Set to `true` to force the reload of routes
     */
    public function loadRoutes(bool $force=false): void
    {
        if ($this->loadedRoutes && (!$force))
            return;

        $this->loadedRoutes = true;
        $this->loadAutoloaderFiles();
        $this->loadControllersRoutes();
    }

    protected function loadAutoloaderFiles(): void
    {
        foreach (Autoloader::getListFiles(Autoloader::ROUTES) as $file)
            require_once $file;
    }

    protected function loadControllersRoutes(): void
    {
        $autoloadFile = Autoloader::getListFiles(Autoloader::AUTOLOAD);

        ObjectArray::fromArray($autoloadFile)
        ->filter(fn($file) => str_contains($file, "Controllers"))
        ->map(Utils::pathToNamespace(...))
        ->filter(fn($class) => Utils::uses($class, Controller::class))
        ->forEach(fn($class) => $class::declareRoutes($this));
    }

    /**
     * Create a Group route that your can re-use with `group()`
     */
    public function createGroup(string|array $urlPrefix, string|array $middlewares): array
    {
        return [
            "path" => Utils::toArray($urlPrefix),
            "middlewares" => Utils::toArray($middlewares),
        ];
    }

    /**
     * Group routes that are declared in given callback
     * @note You can easily create a group by `createGroup()`
     */
    public function groupCallback(array $group, callable $callback): void
    {
        $original = $this->group;

        foreach ($group as $key => $value)
        {
            // Warning: dictionaries need to be merged, we cannot use Utils::toArray here
            $value = is_array($value)? $value: [$value];

            $this->group[$key] = array_merge(
                $this->group[$key] ?? [],
                $value
            );
        }

        $callback($this);

        $this->group = $original;
    }

    /**
     * Apply a given group to given routes
     */
    public function group(array $group, Route ...$routes): array
    {
        if (!count($group))
            return $routes;


        foreach ($routes as &$route)
        {
            if ($groupPrefix = $group["path"] ?? false)
            {
                $groupPrefix = Utils::toArray($groupPrefix);

                $prefix = "/" . join("/", $groupPrefix);
                $route->setPath(str_replace("//", "/", $prefix . $route->getPath()));
            }

            if ($extras = $group["extras"] ?? false)
            {
                $route->setExtras(array_merge($route->getExtras(), $extras));
            }

            if ($middlewares = $group["middlewares"] ?? false)
            {
                $middlewares = Utils::toArray($middlewares);
                $route->addMiddlewares(...$middlewares);
            }
        }

        return $routes;
    }

    public function addGroup(array $group, Route ...$routes): void
    {
        $this->addRoutes(
            ...$this->group($group, ...$routes)
        );
    }

    public function addRoutes(Route ...$routes): void
    {
        array_push(
            $this->routes,
            ...$this->group($this->group, ...$routes)
        );
    }

    protected function findFirstMatchingRoute(Request $req): ?Route
    {
        $this->loadRoutes();
        foreach ($this->routes as $route)
        {
            if (!$route->match($req))
                continue;

            if ($this->isCached())
                $this->putRouteToCache($route, $req);

            return $route;
        }
        return null;
    }

    public function route(Request $request): Response
    {
        $route = $this->getCachedRouteForRequest($request)
              ?? $this->findFirstMatchingRoute($request);

        if (!$route)
        {
            $response = new Response("Page not found", 404, ["Content-Type" => "text/plain"]);
            EventListener::getInstance()->dispatch(new RouteNotFound($request, $response));
            return $response;
        }

        EventListener::getInstance()->dispatch(new RoutedRequest($request, $route));

        return Response::adapt($route($request));
    }

    /**
     * @return array<Route>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @deprecated Use Route->match instead
     */
    public function match(Route $route, Request $request): bool
    {
        return $route->match($request);
    }
}