<?php

namespace Sharp\Classes\Data\Classes;

class QueryField
{
    public function __construct(
        public string $table,
        public string $field,
        public ?string $alias=null
    ) {}

    public function __toString()
    {
        return "`$this->table`.$this->field".( $this->alias ? " as `$this->alias`": "");
    }
}