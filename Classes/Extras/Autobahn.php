<?php

namespace Sharp\Classes\Extras;

use Exception;
use InvalidArgumentException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Events;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Data\DatabaseQuery;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Web\Router;
use Sharp\Core\Utils;

class Autobahn
{
    use Component;

    public ?Router $router = null;

    public function __construct(Router $router=null)
    {
        $this->router = $router ?? Router::getInstance();
    }

    protected function throwOnInvalidModel(string $model): void
    {
        if (!Utils::uses($model, "Sharp\Classes\Data\Model"))
            throw new InvalidArgumentException("[$model] does not use the Model trait !");
    }

    public function all(
        string $model,
        array $createMidddlewares=[],
        array $readMiddlewares=[],
        array $updateMiddlewares=[],
        array $deleteMiddlewares=[]
    )
    {
        $this->create($model, ...$createMidddlewares);
        $this->read($model, ...$readMiddlewares);
        $this->update($model, ...$updateMiddlewares);
        $this->delete($model, ...$deleteMiddlewares);
    }

    public function create(string $model, callable ...$middlewares): void
    {
        list($model, $routeExtras) = $this->makeRequestData($model, ...$middlewares);

        $this->router->addRoutes(
            Route::post($model::getTable(), [self::class, "routeCallbackForCreate"], extras:$routeExtras)
        );
    }

    public function read(string $model, callable ...$middlewares): void
    {
        list($model, $routeExtras) = $this->makeRequestData($model, ...$middlewares);

        $this->router->addRoutes(
            Route::get($model::getTable(), [self::class, "routeCallbackForRead"], extras:$routeExtras)
        );
    }

    public function update(string $model, callable ...$middlewares): void
    {
        list($model, $routeExtras) = $this->makeRequestData($model, ...$middlewares);

        $this->router->addRoutes(
            new Route($model::getTable(), [self::class, "routeCallbackForUpdate"], ["PUT", "PATCH"], [], extras:$routeExtras)
        );
    }

    public function delete(string $model, callable ...$middlewares): void
    {
        list($model, $routeExtras) = $this->makeRequestData($model, ...$middlewares);

        $this->router->addRoutes(
            Route::delete($model::getTable(), [self::class, "routeCallbackForDelete"], extras:$routeExtras)
        );
    }


    protected function makeRequestData(string $model, callable ...$middlewares)
    {
        $this->throwOnInvalidModel($model);
        /** @var \Sharp\Classes\Data\Model $model */
        $routeExtras = ["autobahn-model" => $model, "autobahn-middlewares" => $middlewares];

        return [$model, $routeExtras];
    }

    /**
     * @return array<[\Sharp\Classes\Data\Model,array]>
     */
    protected static function extractRequestData(Request $request)
    {
        $model = $request->getRoute()->getExtras()["autobahn-model"] ?? null;

        $instance = self::getInstance();
        $instance->throwOnInvalidModel($model);

        $middlewares = $request->getRoute()->getExtras()["autobahn-middlewares"] ?? [];

        /** @var \Sharp\Classes\Data\Model $model */
        return [$model, $middlewares];
    }


    public static function routeCallbackForCreate(Request $request)
    {
        $params = $request->all();
        list($model, $middlewares) = self::extractRequestData($request);

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::INSERT);
        $query->setInsertField(array_keys($params));
        $query->insertValues(array_values($params));

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = Events::getInstance();
        $events->dispatch("autobahnCreateBefore", ["model"=>$model, "query"=>$query]);

        $query->fetch();
        $inserted = Database::getInstance()->lastInsertId();

        $events->dispatch("autobahnCreateAfter", ["model"=>$model, "query"=>$query, "insertedId" => $inserted]);

        return Response::json(["insertedId" => $inserted], Response::CREATED);
    }

    public static function routeCallbackForRead(Request $request)
    {
        list($model, $middlewares) = self::extractRequestData($request);

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::SELECT);
        $query->exploreModel(
            $model,
            $request->params("_join") ?? true,
            Utils::toArray($request->params("_ignores") ?? [])
        );

        foreach ($request->all() as $key => $value)
            $query->where($key, $value);

        foreach ($middlewares as $middleware)
            $middleware($query);


        $events = Events::getInstance();
        $events->dispatch("autobahnReadBefore", ["model"=> $model, "query"=> $query]);

        $results = $query->fetch();

        $events->dispatch("autobahnReadAfter", ["model"=> $model, "query"=> $query, "results"=> $results]);

        return Response::json($results);
    }

    public static function routeCallbackForUpdate(Request $request)
    {
        list($model, $middlewares) = self::extractRequestData($request);

        if (!($primaryKey = $model::getPrimaryKey()))
            throw new Exception("Cannot update a model without a primary key");

        if (!($primaryKeyValue = $request->params($primaryKey)))
            return Response::json("A primary key [$primaryKey] is needed to update !", 401);

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::UPDATE);
        $query->where($primaryKey, $primaryKeyValue);

        foreach($request->all() as $key => $value)
            $query->set($key, $value);

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = Events::getInstance();
        $events->dispatch("autobahnUpdateBefore", ["model"=> $model, "query"=> $query]);

        $query->fetch();

        $events->dispatch("autobahnUpdateAfter", ["model"=> $model, "query"=> $query]);

        return Response::json("DONE", Response::CREATED);
    }

    public static function routeCallbackForDelete(Request $request)
    {
        list($model, $middlewares) = self::extractRequestData($request);

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::DELETE);

        if (!count($request->all()))
            return Response::json("At least one filter must be given", Response::CONFLICT);

        foreach ($request->all() as $key => $value)
            $query->where($key, $value);

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = Events::getInstance();
        $events->dispatch("autobahnDeleteBefore", ["model"=> $model, "query"=> $query]);

        $query->fetch();

        $events->dispatch("autobahnDeleteAfter", ["model"=> $model, "query"=> $query]);

        return Response::json("DONE");
    }


}