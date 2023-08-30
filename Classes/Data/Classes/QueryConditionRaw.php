<?php

namespace Sharp\Classes\Data\Classes;

class QueryConditionRaw
{
    public function __construct(
        public string $condition
    ){}

    public function __toString()
    {
        return "($this->condition)";
    }
}