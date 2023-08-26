<?php

namespace Sharp\Classes\Data\Classes;

use Sharp\Classes\Data\Database;

class QueryCondition
{
    public function __construct(
        public string $field,
        public string $value,
        public string $operator="=",
        public ?string $table=null
    ){}

    public function __toString()
    {
        $field = ($this->table ? "`$this->table`." : "") . $this->field;
        return Database::getInstance()->build("$field $this->operator {}", [$this->value]);
    }
}