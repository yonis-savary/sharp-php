<?php

namespace Sharp\Classes\Data;

use InvalidArgumentException;
use Sharp\Classes\Data\DatabaseField;
use Sharp\Core\Utils;

/**
 * Classes that uses `Model` represents tables from your Database
 */
trait Model
{
    protected $data = [];

    /**
     * @return string The table name in your database
     */
    public static function getTable(): string
    {
        return "table";
    }

    /**
     * @return string|null The primary key field name (or null if none)
     */
    public static function getPrimaryKey(): string|null
    {
        return "id";
    }

    /**
     * @return array<string,DatabaseField> Associative array with name => field description (DatabaseField object)
     */
    public static function getFields(): array
    {
        return [];
    }

    final public static function getFieldNames(): array
    {
        return array_keys(self::getFields());
    }

    public static function getInsertables(): array
    {
        $fields = self::getFields();
        $insertables = [];
        foreach ($fields as $name => $_)
        {
            if ($name = self::getPrimaryKey())
                continue;

            $insertables[] = $name;
        }
        return $insertables;
    }

    public function __construct(array $data=[])
    {
        $fields = self::getFieldNames();
        $this->data = [];

        foreach ($fields as $field)
        {
            if (!array_key_exists($field, $data))
                continue;

            $this->data[$field] = $data[$field];
        }
    }

    /**
     * Start a DatabaseQuery to insert values in the model's table
     */
    public static function insert(): DatabaseQuery
    {
        $query = new DatabaseQuery(self::getTable(), DatabaseQuery::INSERT);
        $query->setInsertField(self::getFieldNames());

        return $query;
    }

    /**
     * Start a DatabaseQuery to select rows from the model's table
     */
    public static function select(bool $recursive=true, array $foreignKeyIgnores=[]): DatabaseQuery
    {
        $query = new DatabaseQuery(self::getTable(), DatabaseQuery::SELECT);
        $query->exploreModel(self::class, $recursive, $foreignKeyIgnores);

        return $query;
    }

    /**
     * Select every row that respects given conditions
     *
     * @param array $conditions Column conditions as <column> => <value>
     * @param bool $recursive Explore foreign keys to fetch references
     * @param array $foreignKeyIgnores List of foreign keys to ignore while exploring model as "table&foreign_key_column"
     * @example base `Model::selectWhere(["id" => 309, "user" => 585])`
     */
    public static function selectWhere(array $conditions=[], bool $recursive=true, array $foreignKeyIgnores=[]): array
    {
        if (!Utils::isAssoc($conditions))
            throw new InvalidArgumentException('$conditions must be an associative array as <column> => <value>');

        $query = self::select($recursive, $foreignKeyIgnores);

        foreach ($conditions as $column => $value)
            $query->where($column, $value);

        return $query->fetch();
    }

    /**
     * Start a DatabaseQuery to update row(s) of the model's table
     */
    public static function update(): DatabaseQuery
    {
        return new DatabaseQuery(self::getTable(), DatabaseQuery::UPDATE);
    }

    /**
     * Start a DatabaseQuery to delete row(s) from the model's table
     */
    public static function delete(): DatabaseQuery
    {
        return new DatabaseQuery(self::getTable(), DatabaseQuery::DELETE);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Insert a row of data in the model's table
     *
     * @param array $data Associative array (with `field => value`) to insert
     * @param Database $database Database to use (global instance if `null`)
     * @return int|false Return the inserted Id or false on failure
     */
    public static function insertArray(array $data, Database $database=null): int|false
    {
        if (!Utils::isAssoc($data))
            throw new InvalidArgumentException("Given data must be an associative array !");

        $fields = array_keys($data);
        $modelFields = self::getFieldNames();

        $invalidFields = array_diff($fields, $modelFields);
        if (count($invalidFields))
        {
            $invalidFields = join(", ", $invalidFields);
            throw new InvalidArgumentException(self::class . " model does not contains these fields: $invalidFields");
        }

        $database ??= Database::getInstance();

        $insert = new DatabaseQuery(self::getTable(), DatabaseQuery::INSERT);
        $insert->setInsertField($fields);
        $insert->insertValues(array_values($data));
        $insert->fetch($database);

        return $database->lastInsertId();
    }

    /**
     * Select a row where the primary key is the one given
     *
     * @param mixed $id Id to select
     * @param bool $explore Explore foreign keys to fetch references
     * @return ?array Matching row or `null`
     */
    public static function findId(mixed $id, bool $explore=true): ?array
    {
        return self::find(self::getPrimaryKey(), $id, $explore);
    }

    /**
     * Select the first row where `$column` equal `$value`
     *
     * @param mixed $column Filter column
     * @param mixed $value Value to match
     * @param bool $explore Explore foreign keys to fetch references
     * @return ?array Matching row or `null`
     */
    public static function find(string $column, mixed $value, bool $explore=true): ?array
    {
        return self::select($explore)->where($column, $value, table: self::getTable())->first();
    }


    /**
     * Select the first row where conditions from $condition are matched
     *
     * @param array $conditions Column conditions as <column> => <value>
     * @param bool $explore Explore foreign keys to fetch references
     * @example base `Model::findWhere(["id" => 309, "user" => 585])`
     */
    public static function findWhere(array $conditions, bool $explore=true): ?array
    {
        if (!Utils::isAssoc($conditions))
            throw new InvalidArgumentException('$conditions must be an associative array as <column> => <value>');

        $query = self::select($explore);

        foreach ($conditions as $column => $value)
            $query->where($column, $value);

        return $query->first();
    }


    /**
     * Check the existence of any row where conditions from $condition are matched
     *
     * @param array $conditions Column conditions as <column> => <value>
     */
    public static function existsWhere(array $condition, bool $explore=false): bool
    {
        return self::findWhere($condition, $explore) !== null;
    }

    /**
     * Check the existence of any row where the primary key matches the given one
     *
     * @param array $conditions Column conditions as <column> => <value>
     */
    public static function idExists($idOrPrimaryKeyValue): bool
    {
        return self::existsWhere([self::getPrimaryKey() => $idOrPrimaryKeyValue], false);
    }

    /**
     * Return a new `DatabaseQuery` to update a specific row
     *
     * @param mixed $id Id to select
     * @return DatabaseQuery Base query to work with
     */
    public static function updateId(mixed $id): DatabaseQuery
    {
        return self::update()->where(self::getPrimaryKey(), $id);
    }

    /**
     * Directly update a row in the model table
     *
     * @param mixed $id Unique value of the primary key field
     * @param array $columns Updated columns, associative array as `field => new value`
     */
    public static function updateRow(mixed $id, array $columns): void
    {
        $query = self::updateId($id);

        foreach ($columns as $field => $value)
            $query->set($field, $value);

        $query->fetch();
    }

    /**
     * Delete specific row following the primary key
     *
     * @param mixed $id Id/primary key to select
     */
    public static function deleteId(mixed $id): void
    {
        self::delete()->where(self::getPrimaryKey(), $id)->fetch();
    }

    /**
     * Delete every row where conditions from $condition are matched
     *
     * @param array $conditions Column conditions as <column> => <value>
     * @param bool $explore Explore foreign keys to fetch references
     * @example base `Model::deleteWhere(["id" => 309, "user" => 585])`
     */
    public static function deleteWhere(array $conditions): void
    {
        if (!Utils::isAssoc($conditions))
            throw new InvalidArgumentException('$conditions must be an associative array as <column> => <value>');

        $query = self::delete();

        foreach ($conditions as $column => $value)
            $query->where($column, $value);

        $query->fetch();
    }

    public static function validate(array $data=null): bool
    {
        foreach (self::getFields() as $fieldName => $fieldObject)
        {
            $value = $data[$fieldName] ?? null;
            if (!$fieldObject->validate($value))
                return false;
        }
        return true;
    }

    public function __get(string $prop): mixed
    {
        if (!array_key_exists($prop, $this->data))
            throw new InvalidArgumentException("Unknown property [$prop]");

        return $this->data[$prop];
    }
}