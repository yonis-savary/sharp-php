<?php

namespace Sharp\Classes\Data\Classes;

class QueryJoin
{
    public function __construct(
        public string $mode,
        public QueryField $source,
        public string $joinOperator,
        public string $table,
        public string $alias,
        public string $targetField
    ){ }

    public function __toString()
    {
        return "$this->mode JOIN `$this->table` AS `$this->alias` ON $this->source $this->joinOperator `$this->alias`.$this->targetField";
    }
}