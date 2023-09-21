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
        return (new DatabaseQuery(self::getTable(), DatabaseQuery::INSERT))->setInsertField(self::getFieldNames());
    }

    /**
     * Start a DatabaseQuery to select rows from the model's table
     */
    public static function select(bool $recursive=true, array $foreignKeyIgnores=[]): DatabaseQuery
    {
        $query = new DatabaseQuery(self::getTable(), DatabaseQuery::SELECT);
        $query->exploreModel(self::class, $recursive, $foreignKeyIgnores
    );
        return $query;
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
     * @param int $id Id to select
     * @return ?array Matching row or `null` otherwise
     */
    public static function fromId(mixed $id): ?array
    {
        return self::select()->where(self::getPrimaryKey(), $id)->first();
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
        if (array_key_exists($prop, $this->data))
            return $this->data[$prop];
        throw new InvalidArgumentException("Unknown propety [$prop]");
    }
}