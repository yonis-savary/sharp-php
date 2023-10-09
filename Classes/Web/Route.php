<?php

namespace Sharp\Classes\Web;

use Exception;
use RuntimeException;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Renderer;
use Sharp\Core\Utils;

class Route
{
    const SLUG_FORMATS = [
        "int"      => "\d+",
        "float"    => "\d+(?:\.\d+)?",
        "any"      => ".+",
        "date"     => "\d{4}\-\d{2}\-\d{2}",
        "time"     => "\d{2}\:\d{2}\:\d{2}",
        "datetime" => "\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}",
    ];

    protected $callback;

    protected string $path;
    protected ?array $methods=[];
    protected ?array $extras=[];

    /** @var array<MiddlewareInterface> $middlewares */
    protected array $middlewares = [];

    public static function any(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    {
        return new self($path, $callback, [], $middlewares, $extras);
    }

    public static function get(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    {
        return new self($path, $callback, ["GET"], $middlewares, $extras);
    }

    public static function post(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    {
        return new self($path, $callback, ["POST"], $middlewares, $extras);
    }

    public static function patch(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    {
        return new self($path, $callback, ["PATCH"], $middlewares, $extras);
    }

    public static function put(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    {
        return new self($path, $callback, ["PUT"], $middlewares, $extras);
    }

    public static function delete(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    {
        return new self($path, $callback, ["DELETE"], $middlewares, $extras);
    }

    public static function view(string $path, string $template, array $middlewares=[], array $context=[], array $extras=[]): self
    {
        return new self($path, [self::class, "renderViewCallback"], ["GET"], $middlewares, array_merge($extras, ["template" => $template, "context" => $context]));
    }

    public static function redirect(string $path, string $target, array $middlewares=[], array $extras=[]): self
    {
        return new self($path, [self::class, "redirectRequestToTarget"], [], $middlewares, array_merge($extras, ["redirection-target" => $target]));
    }

    public static function file(string $path, string $target, array $middlewares=[], array $extras=[]): self
    {
        return new self($path, [self::class, "serveFile"], [], $middlewares, array_merge($extras, ["file" => $target]));
    }

    public static function renderViewCallback(Request $request)
    {
        $extras = $request->getRoute()->getExtras();

        return Renderer::getInstance()->render(
            $extras["template"],
            [
                ...($extras["context"] ?? []),
                "request" => $request
            ]
        );
    }

    public static function redirectRequestToTarget(Request $request)
    {
        $extras = $request->getRoute()->getExtras();
        return Response::redirect(
            $extras["redirection-target"]
        );
    }

    public static function serveFile(Request $request)
    {
        $extras = $request->getRoute()->getExtras();
        $target = Utils::relativePath($extras["file"]);

        if (!is_file($target))
            throw new RuntimeException("[$target] File does not exists !");

        return Response::file($target);
    }

    public function __construct(
        string $path,
        callable $callback,
        ?array $methods=[],
        array $middlewares=[],
        ?array $extras=[]
    ) {
        $this->path = $path;
        $this->methods = $methods ?? [];
        $this->extras = $extras ?? [];
        $this->callback = $callback;
        $this->setMiddlewares($middlewares);

        if (!str_starts_with($this->path, "/"))
            $this->path = "/" . $this->path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(array $middlewares)
    {
        $this->middlewares = [];
        $this->addMiddlewares(...$middlewares);
    }

    public function getExtras(): array
    {
        return $this->extras;
    }

    public function setExtras(array $extras)
    {
        $this->extras = $extras;
    }

    public function addMiddlewares(string ...$middlewares)
    {
        foreach ($middlewares as $middleware)
        {
            if (!Utils::implements($middleware, MiddlewareInterface::class))
                throw new Exception("Cannot use [$middleware] as middleware (must implements [".MiddlewareInterface::class."])");
        }
        array_push($this->middlewares, ...$middlewares);
    }

    protected function matchPathRegex(Request $request): string
    {
        $regexMap = [];
        $parts = explode("/", $this->path);

        foreach ($parts as &$part)
        {
            if (!preg_match("/^\{.+\}$/", $part))
                continue;

            $part = substr($part, 1, strlen($part)-2);

            $name = $part;
            $expression = "[^\\/]+";

            if (str_contains($part, ":"))
            {
                list($type, $name) = explode(":", $part, 2);
                $expression = self::SLUG_FORMATS[$type] ?? $type;
            }

            $regexMap[] = $name;
            $part = "($expression)";
        }

        $regex = "/^". join("\\/", $parts) ."$/";

        if (!preg_match($regex, $request->getPath(), $slugs))
            return false;

        $namedSlugs = [];
        array_shift($slugs);
        for ($i=0; $i<count($slugs); $i++)
            $namedSlugs[$regexMap[$i]] = urldecode($slugs[$i]);

        $request->setSlugs($namedSlugs);
        return true;
    }

    public function match(Request $request): bool
    {
        if (count($this->methods))
        {
            if (!in_array($request->getMethod(), $this->methods))
                return false;
        }

        $routePath = $this->path;
        $requestPath = $request->getPath();

        // Little optimization: if the route has no slug
        // we can just compare strings, no need to process anything
        if (!str_contains($routePath, "{"))
            return $routePath === $requestPath;

        return $this->matchPathRegex($request);
    }

    public function __invoke(Request $request): mixed
    {
        $request->setRoute($this);

        foreach ($this->middlewares as $middleware)
        {
            $middlewareResponse = $middleware::handle($request);

            if ($middlewareResponse instanceof Response)
                return $middlewareResponse;

            $request = $middlewareResponse;
        }

        return ($this->callback)($request, ...array_values($request->getSlugs()));
    }
}