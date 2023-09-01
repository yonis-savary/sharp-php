<?php

namespace Sharp\Classes\Extras;

use Exception;
use InvalidArgumentException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Data\DatabaseQuery;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Web\Router;
use Sharp\Core\Utils;

class Autobahn
{
    use Component, Configurable;

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
        $this->throwOnInvalidModel($model);
        $table = $model::getTable();
        $this->router->addRoutes(
            Route::post("/$table", function(Request $req) use ($model, $middlewares)
            {
                $params = $req->all();
                $query = new DatabaseQuery($model::getTable(), DatabaseQuery::INSERT);
                $query->setInsertField(...array_keys($params));
                $query->insertValues(...array_values($params));

                foreach ($middlewares as $middleware)
                    $middleware($query);

                return $query->fetch();
            }
        ));
    }

    public function read(string $model, callable ...$middlewares): void
    {
        $this->throwOnInvalidModel($model);
        $table = $model::getTable();
        $this->router->addRoutes(
            Route::get("/$table", function(Request $req) use ($model, $middlewares)
            {
                $query = new DatabaseQuery($model::getTable(), DatabaseQuery::SELECT);
                if ($req->params("_join") ?? true)
                    $query->exploreModel($model);

                foreach ($req->all() as $key => $value)
                    $query->where($key, $value);

                foreach ($middlewares as $middleware)
                    $middleware($query);

                return $query->fetch();
            }
        ));
    }

    public function update(string $model, callable ...$middlewares): void
    {
        $this->throwOnInvalidModel($model);
        $table = $model::getTable();
        $this->router->addRoutes(
            new Route("/$table", function(Request $req) use ($model, $middlewares)
            {
                $primaryKey = $model::getPrimaryKey();

                if (!$primaryKey)
                    throw new Exception("Cannot update a model without a primary key");

                $primaryKeyValue = $req->params($primaryKey);
                if (!$primaryKeyValue)
                    return Response::json("A primary key [$primaryKey] is needed to update !", 401);

                $query = new DatabaseQuery($model::getTable(), DatabaseQuery::UPDATE);

                foreach($req->all() as $key => $value)
                    $query->set($key, $value);

                $query->where($primaryKey, $primaryKeyValue);

                foreach ($middlewares as $middleware)
                    $middleware($query);

                return $query->fetch();
            }, ["PUT", "PATCH"]
        ));
    }

    public function delete(string $model, callable ...$middlewares): void
    {
        $this->throwOnInvalidModel($model);
        $table = $model::getTable();
        $this->router->addRoutes(
            Route::delete("/$table", function(Request $req) use ($model, $middlewares)
            {
                $query = new DatabaseQuery($model::getTable(), DatabaseQuery::DELETE);

                foreach ($req->all() as $key => $value)
                    $query->where($key, $value);

                foreach ($middlewares as $middleware)
                    $middleware($query);

                return $query->fetch();
            }
        ));
    }
}