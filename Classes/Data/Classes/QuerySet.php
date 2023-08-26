<?php

namespace Sharp\Classes\Data\Classes;

use Sharp\Classes\Data\Database;

class QuerySet
{
    public function __construct(
        public string $field,
        public string $value,
        public ?string $table=null
    ) { }

    public function __toString()
    {
        return Database::getInstance()->build(
            ($this->table ? "`$this->table`." : "") . $this->field." = {}",
            [$this->value]
        );
    }
}