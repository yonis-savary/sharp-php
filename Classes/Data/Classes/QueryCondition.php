<?php

namespace Sharp\Classes\Data\Classes;

use Sharp\Classes\Data\Database;

class QueryCondition
{
    public function __construct(
        public string $field,
        public mixed $value,
        public string $operator="=",
        public ?string $table=null
    ){}

    public function __toString()
    {
        $field = ($this->table ? "`$this->table`." : "") . $this->field;

        if ($this->operator === "=" && $this->value === null)
            $this->operator = "IS";
        if ($this->operator === "<>" && $this->value === null)
            $this->operator = "IS NOT";

        return Database::getInstance()->build("($field $this->operator {})", [$this->value]);
    }
}