<?php

namespace Sharp\Classes\Data\Classes;

class QueryJoin
{
    public function __construct(
        public string $mode,
        public string $table,
        public string $alias,
        public QueryField $source,
        public string $targetField,
        public string $joinOperator="="
    ){ }

    public function __toString()
    {
        return "$this->mode JOIN `$this->table` as `$this->alias` ON $this->source $this->joinOperator `$this->alias`.$this->targetField";
    }
}