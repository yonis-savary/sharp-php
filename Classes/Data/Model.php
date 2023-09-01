<?php

namespace Sharp\Classes\Data;

use InvalidArgumentException;
use Sharp\Classes\Data\DatabaseField;

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

    public static function insert(): DatabaseQuery
    {
        return (new DatabaseQuery(self::getTable(), DatabaseQuery::INSERT))->setInsertField(self::getTable(), ...self::getFieldNames());
    }

    public static function select(): DatabaseQuery
    {
        $query = new DatabaseQuery(self::getTable(), DatabaseQuery::SELECT);
        $query->exploreModel(self::class);
        return $query;
    }

    public static function update(): DatabaseQuery
    {
        return new DatabaseQuery(self::getTable(), DatabaseQuery::UPDATE);
    }

    public static function delete(): DatabaseQuery
    {
        return new DatabaseQuery(self::getTable(), DatabaseQuery::DELETE);
    }

    public function toArray(): array
    {
        return $this->data;
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