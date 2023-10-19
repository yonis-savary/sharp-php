<?php

namespace Sharp\Classes\Extras;

use Exception;
use InvalidArgumentException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Extras\AutobahnDrivers\BaseDriver;
use Sharp\Classes\Extras\AutobahnDrivers\DriverInterface;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Web\Router;
use Sharp\Core\Utils;
use Sharp\Classes\Data\Model;

class Autobahn
{
    use Component, Configurable;

    public ?Router $router = null;

    public static function getDefaultConfiguration(): array
    {
        return ["driver" => BaseDriver::class];
    }

    public function __construct(Router $router=null)
    {
        $this->loadConfiguration();
        $this->router = $router ?? Router::getInstance();

        $driver = $this->configuration["driver"];

        if (!Utils::implements($driver, DriverInterface::class))
            throw new Exception("Autobahn driver must implements ". DriverInterface::class);
    }

    /**
     * @return \Sharp\Classes\Data\Model
     */
    public function throwOnInvalidModel(string $model)
    {
        if (!Utils::uses($model, Model::class))
            throw new InvalidArgumentException("[$model] does not use the Model trait !");

        return $model;
    }

    public function all(
        string $model,
        array $createMiddlewares=[],
        array $createMultiplesMiddlewares=[],
        array $readMiddlewares=[],
        array $updateMiddlewares=[],
        array $deleteMiddlewares=[]
    )
    {
        $this->create($model, ...$createMiddlewares);
        $this->createMultiples($model, ...$createMultiplesMiddlewares);
        $this->read($model, ...$readMiddlewares);
        $this->update($model, ...$updateMiddlewares);
        $this->delete($model, ...$deleteMiddlewares);
    }

    /**
     * @return array[\Sharp\Classes\Data\Model,array]
     */
    protected function getNewRouteExtras(string $model, callable ...$middlewares): array
    {
        return ["autobahn-model" => $model, "autobahn-middlewares" => $middlewares];
    }

    protected function addRoute(
        string $model,
        array $middlewares,
        string $callback,
        array $methods,
        string $suffix=""
    ): void
    {
        $routeExtras = $this->getNewRouteExtras($model, ...$middlewares);
        $model = $this->throwOnInvalidModel($model);

        $driver = $this->configuration["driver"];

        $this->router->addRoutes(
            new Route(
                $model::getTable() . $suffix,
                [$driver, $callback],
                $methods,
                [],
                $routeExtras
            )
        );
    }

    public function create(string $model, callable ...$middlewares): void
    {
        $this->addRoute($model, $middlewares, "createCallback", ["POST"]);
    }

    public function createMultiples(string $model, callable ...$middlewares): void
    {
        $this->addRoute($model, $middlewares, "multipleCreateCallback", ["POST"], "/create-multiples");
    }

    public function read(string $model, callable ...$middlewares): void
    {
        $this->addRoute($model, $middlewares, "readCallback", ["GET"]);
    }

    public function update(string $model, callable ...$middlewares): void
    {
        $this->addRoute($model, $middlewares, "updateCallback", ["PUT", "PATCH"]);
    }

    public function delete(string $model, callable ...$middlewares): void
    {
        $this->addRoute($model, $middlewares, "deleteCallback", ["DELETE"]);
    }
}