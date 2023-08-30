<?php

namespace Sharp\Classes\Web;

use Exception;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Web\Renderer;
use Sharp\Core\Utils;
use Throwable;

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

    /** @var array<MiddlewareInterface> $middlewares */
    protected array $middlewares = [];

    public static function get(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    { return new self($path, $callback, ["GET"], $middlewares, $extras); }

    public static function post(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    { return new self($path, $callback, ["POST"], $middlewares, $extras); }

    public static function patch(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    { return new self($path, $callback, ["PATCH"], $middlewares, $extras); }

    public static function put(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    { return new self($path, $callback, ["PUT"], $middlewares, $extras); }

    public static function delete(string $path, callable $callback, array $middlewares=[], ?array $extras=[]): self
    { return new self($path, $callback, ["DELETE"], $middlewares, $extras); }

    public static function view(string $path, string $template, array $middlewares=[], array $context=[], array $extras=[]): self
    { return new self($path, [self::class, "renderViewCallback"], ["GET"], $middlewares, array_merge($extras, ["template" => $template, "context" => $context])); }

    public static function redirect(string $path, string $target, array $extras=[]): self
    { return new self($path, [self::class, "redirectRequestToTarget"], [], [], array_merge($extras, ["redirection-target" => $target])); }

    public static function renderViewCallback(Request $request)
    {
        $route = $request->getRoute();
        $template = $route->getExtras()["template"];
        $context = $route->getExtras()["context"] ?? [];

        return Renderer::getInstance()->render($template, $context);
    }

    public static function redirectRequestToTarget(Request $request)
    {
        $route = $request->getRoute();
        return Response::redirect($route->getExtras()["redirection-target"]);
    }

    public function __construct(
        protected string $path,
        callable $callback,
        protected ?array $methods=[],
        array $middlewares=[],
        protected ?array $extras=[]
    ) {
        $this->callback = $callback;
        $this->addMiddlewares(...$middlewares);
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
        foreach ($middlewares as $m)
            $this->addMiddlewares($m);
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

    public function match(Request $request): bool
    {
        if (count($this->methods))
        {
            if (!in_array($request->getMethod(), $this->methods))
                return false;
        }

        $path = $request->getPath();

        $regex = $this->path;
        $regexMap = [];

        $parts = explode("/", $this->path);

        foreach ($parts as &$part)
        {
            if (!preg_match("/\{((.+?):)?(.+?)\}/",  $part, $match))
            {
                $part = preg_quote($part);
                continue;
            }

            $expression = "[^\\\\]+";
            if ($match[2])
                $expression = self::SLUG_FORMATS[$match[2]] ?? $expression;

            $name = $match[3];
            $regexMap[] = $name;
            $part = "($expression)";
        }

        $regex = "/^". join("\\/", $parts) ."$/";

        $slugs = [];

        if (!preg_match($regex, $path, $slugs))
            return false;

        $request->setSlugs(array_slice($slugs, 1));
        return true;
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

        try
        {
            return ($this->callback)($request, ...array_values($request->getSlugs()));
        }
        catch(Throwable $err)
        {
            // Error message
            Logger::getInstance()->logThrowable($err);
            throw $err;
        }
    }
}