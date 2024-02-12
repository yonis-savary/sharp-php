<?php

namespace Sharp\Classes\Extras\AutobahnDrivers;

use Exception;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Data\DatabaseQuery;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Extras\Autobahn;
use Sharp\Classes\Http\Classes\ResponseCodes;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Core\Utils;
use Sharp\Classes\Events\AutobahnEvents\AutobahnCreateAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnCreateBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnDeleteAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnDeleteBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnMultipleCreateAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnMultipleCreateBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnReadAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnReadBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnUpdateAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnUpdateBefore;

class BaseDriver implements DriverInterface
{
    /**
     * Extract model name and middlewares from a route extras
     * @return array[\Sharp\Classes\Data\Model,array]
     */
    protected static function extractRouteData(Request $request)
    {
        $extras = $request->getRoute()->getExtras();

        $model = $extras["autobahn-model"] ?? null;
        $model = Autobahn::getInstance()->throwOnInvalidModel($model);

        $middlewares = $extras["autobahn-middlewares"] ?? [];

        return [$model, $middlewares];
    }

    public static function createCallback(Request $request): Response
    {
        list($model, $middlewares) = self::extractRouteData($request);

        $params = $request->all();
        foreach ($middlewares as $middleware)
            $middleware($params);

        $fields = array_keys($params);
        $values = array_values($params);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnCreateBefore($model, $fields, $values));

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::INSERT);
        $query->setInsertField($fields);
        $query->insertValues($values);
        $query->fetch();
        $inserted = Database::getInstance()->lastInsertId();

        $events->dispatch(new AutobahnCreateAfter($model, $fields, $values, $query, $inserted));

        return Response::json(["insertedId"=>$inserted], ResponseCodes::CREATED);
    }

    public static function multipleCreateCallback(Request $request): Response
    {
        list($model, $middlewares) = self::extractRouteData($request);

        $data = $request->body();

        if (!is_array($data))
            return Response::json('Only Arrays or objects are allowed !', 400);

        $data = Utils::toArray($data);

        $fields = array_keys($data[0]);
        $badFields = array_diff($fields, $model::getFieldNames()) ;
        if (count($badFields))
            return Response::json("[$model] does not contains theses fields " . json_encode($badFields), 400);

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::INSERT);
        $query->setInsertField($fields);

        $data = ObjectArray::fromArray($data);
        foreach ($middlewares as $middleware)
            $data = $data->filter($middleware);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnMultipleCreateBefore($data));

        $data->forEach(function($element) use (&$query) {
            $query->insertValues(array_values($element));
        });

        $query->fetch();
        $lastInsert = Database::getInstance()->lastInsertId();
        $insertedIdList = range($lastInsert-$data->length()+1, $lastInsert);

        $events->dispatch(new AutobahnMultipleCreateAfter($model, $query, $insertedIdList));

        return Response::json(['insertedId' => $insertedIdList]);
    }

    public static function readCallback(Request $request): Response
    {
        list($model, $middlewares) = self::extractRouteData($request);

        $doJoin = ($request->params("_join") ?? true) == true;
        $ignores = Utils::toArray($request->params("_ignores") ?? []);
        list($limit, $offset) = $request->list("_limit", "_offset");
        $request->unset(["_ignores", "_join", "_limit", "_offset"]);

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::SELECT);
        $query->exploreModel($model, $doJoin, $ignores);

        if ($limit)
            $query->limit($limit);

        if ($offset)
            $query->offset($offset);

        foreach ($request->all() as $key => $value)
            $query->where($key, $value);

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnReadBefore($model, $query));

        $results = $query->fetch();

        $events->dispatch(new AutobahnReadAfter($model, $query, $results));

        return Response::json($results);
    }



    public static function updateCallback(Request $request): Response
    {
        list($model, $middlewares) = self::extractRouteData($request);

        if (!($primaryKey = $model::getPrimaryKey()))
            throw new Exception("Cannot update a model without a primary key");

        if (!($primaryKeyValue = $request->params($primaryKey)))
            return Response::json("A primary key [$primaryKey] is needed to update !", 401);

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::UPDATE);
        $query->where($primaryKey, $primaryKeyValue);

        foreach($request->all() as $key => $value)
        {
            if ($key === $primaryKey)
                continue;
            $query->set($key, $value);
        }

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnUpdateBefore($model, $primaryKeyValue, $query));

        $query->fetch();

        $events->dispatch(new AutobahnUpdateAfter($model, $primaryKeyValue, $query));

        return Response::json("DONE", ResponseCodes::CREATED);
    }


    public static function deleteCallback(Request $request): Response
    {
        list($model, $middlewares) = self::extractRouteData($request);

        $query = new DatabaseQuery($model::getTable(), DatabaseQuery::DELETE);

        if (!count($request->all()))
            return Response::json("At least one filter must be given", ResponseCodes::CONFLICT);

        foreach ($request->all() as $key => $value)
            $query->where($key, $value);

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnDeleteBefore($model, $query));

        $query->fetch();

        $events->dispatch(new AutobahnDeleteAfter($model, $query));

        return Response::json("DONE");
    }
}