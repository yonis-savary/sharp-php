<?php

namespace Sharp\Classes\Data\Classes;

class QueryOrder
{
    public function __construct(
        public QueryField $field,
        public string $sortMode="ASC"
    ) {}

    public function __toString()
    {
        return "$this->field $this->sortMode";
    }
}