<?php

namespace Sharp\Classes\Data\Classes;

use Sharp\Classes\Data\DatabaseField;

class QueryField
{
    public function __construct(
        public string $table,
        public string $field,
        public ?string $alias=null,
        public int $type=DatabaseField::STRING
    ) {}

    public function __toString()
    {
        return "`$this->table`.$this->field".( $this->alias ? " as `$this->alias`": "");
    }

    public function fromString(mixed $value)
    {
        if ($value === null)
            return null;

        switch ($this->type)
        {
            case DatabaseField::STRING:
                return $value;
            case DatabaseField::INTEGER:
                return intval($value);
            case DatabaseField::FLOAT:
                return floatval($value);
            case DatabaseField::BOOLEAN:
                $value = strtolower("$value");
                return in_array($value, ["1", "true"]);
            case DatabaseField::DECIMAL:
                return $value;
        }
    }
}