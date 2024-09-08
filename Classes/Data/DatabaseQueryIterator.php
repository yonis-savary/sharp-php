<?php 

namespace Sharp\Classes\Data;

/**
 * @warning (WIP) This class is not tested yet, use it at your own risk
 */
class DatabaseQueryIterator
{
    protected int $count;
    protected int $index;
    protected DatabaseQuery $query;

    public static function forEach(DatabaseQuery $query, callable $function): void
    {
        $iterator = new self($query);

        while ($data = $iterator->next())
            $function($data, $iterator->getLastIndex(), $iterator->getCount());
    }

    public function __construct(DatabaseQuery $query)
    {
        $sql = $query->build();
        $this->query = $query;
        $this->count = Database::getInstance()->query("SELECT COUNT(*) as c FROM ($sql) as _ti")[0]["c"] ?? 0;
        $this->index = 0;
    }

    public function getLastIndex(): int 
    {
        $index = $this->index;
        return $index == 0 ? 0 : $index-1;
    }

    public function getCount(): int 
    {
        return $this->count;
    }

    public function next(): array|false 
    {
        if ($this->index >= $this->count)
            return false;

        $array = $this->query->limit(1, $this->index)->fetch()[0]["data"] ?? false;
            
        $this->index++;
        return $array;
    }
}