<?php

namespace Sharp\Classes\Data;

use Exception;
use InvalidArgumentException;
use PDO;
use Sharp\Classes\Data\Classes\QueryCondition;
use Sharp\Classes\Data\Classes\QueryField;
use Sharp\Classes\Data\Classes\QueryJoin;
use Sharp\Classes\Data\Classes\QueryOrder;
use Sharp\Classes\Data\Classes\QuerySet;
use Sharp\Classes\Data\Classes\QueryConditionRaw;
use Sharp\Classes\Data\Database;
use Sharp\Core\Utils;

class DatabaseQuery
{
    const INSERT = 1;
    const SELECT = 2;
    const UPDATE = 3;
    const DELETE = 4;

    /** @todo Put this in configuration instead */
    const JOIN_LIMIT = 50;

    protected int $mode;

    /** @var array<QueryField> $fields */
    protected array $fields = [];

    /** @var array<QueryCondition> $conditions */
    protected array $conditions = [];

    /** @var array<QueryJoin> $joins */
    protected array $joins = [];

    /** @var array<QueryOrder> $joins */
    protected array $orders = [];

    protected array $updates = [];

    protected array $insertFields = [];
    protected array $insertValues = [];

    protected string $targetTable;

    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(
        string $table,
        int $mode
    ){
        $this->targetTable = $table;
        $this->setMode($mode);
    }

    public function set(
        string $field,
        string $value,
        string $table=null,
    ): self
    {
        $this->updates[] = new QuerySet($field, $value, $table);
        return $this;
    }

    public function setInsertField(string ...$fields): self
    {
        $this->insertFields = $fields;
        return $this;
    }

    public function insertValues(mixed ...$values): self
    {
        if (!count($this->insertFields))
            throw new Exception("Cannot insert values until insert fields are defined");

        if (count($values) !== count($this->insertFields))
            throw new Exception(sprintf("Cannot insert %s values, %s expected", [count($values), count($this->insertFields)]));

        $template = "(". join(",", array_fill(0, count($values), "{}")) .")";
        $template = Database::getInstance()->build($template, $values);
        $this->insertValues[] = $template;
        return $this;
    }

    public function addField(
        string $table,
        string $field
    ): self
    {
        $this->fields[] = new QueryField($table, $field);
        return $this;
    }

    public function exploreModel(string $model): self
    {
        if (!Utils::uses($model, "Sharp\Classes\Data\Model"))
            throw new InvalidArgumentException("[$model] must use model trait");

        $references = [];

        $table = $model::getTable();
        $fields = $model::getFields();
        /** @var DatabaseField $field */
        foreach ($fields as $_ => $field)
        {
            $this->addField($table, $field->name);

            if (!($ref = $field->reference))
                continue;

            $references[] = [
                $table,
                $field->name,
                ...$ref,
                [$table]
            ];
        }

        $this->exploreReferences($references);
        return $this;
    }


    public function limit(int $limit, int $offset=null): self
    {
        $this->limit = $limit;
        if ($offset) $this->offset($offset);
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    protected function exploreReferences($references): void
    {
        $nextReferences = [];

        foreach ($references as [$origin, $field, $model, $target, $tableAcc])
        {
            $targetAcc = "$origin&$field";
            $this->joins[] = new QueryJoin(
                "LEFT",
                $model::getTable(),
                $targetAcc,
                new QueryField($origin, $field),
                $target
            );

            if (count($this->joins) == self::JOIN_LIMIT)
                return;

            /** @var DatabaseField $field */
            foreach ($model::getFields() as $_ => $field)
            {
                $this->addField($targetAcc, $field->name);

                if (!($ref = $field->reference))
                    continue;

                $nextTarget = $ref[0];

                if (in_array($nextTarget, $tableAcc))
                    continue;

                $tableAcc[] = $nextTarget;

                $nextReferences[] = [
                    $targetAcc,
                    $field->name,
                    ...$ref,
                    $tableAcc
                ];
            }

        }
        if (count($nextReferences))
            $this->exploreReferences($nextReferences);
    }


    protected function setMode(int $mode): self
    {
        if (!in_array($mode, [self::INSERT, self::SELECT, self::UPDATE, self::DELETE]))
            throw new InvalidArgumentException("Given mode must be a DatabaseQuery constant !");

        $this->mode = $mode;
        return $this;
    }

    public function where(
        string $field,
        string $value,
        string $operator = "=",
        string $table = null
    ) : self {
        if (!$table)
        {
            $compatibles = array_filter($this->fields, fn($f) => $f->field == $field);
            if (count($compatibles) > 1)
                $table = $compatibles[0]->table;
        }

        $this->conditions[] = new QueryCondition(
            $field,
            $value,
            $operator,
            $table
        );
        return $this;
    }

    public function whereSQL(string $condition): self
    {
        $this->conditions[] = new QueryConditionRaw($condition);
        return $this;
    }

    public function join(
        string $mode,
        string $table,
        string $alias,
        QueryField $source,
        string $targetField,
        string $joinOperator="="
    ): self {
        if (count($this->joins)+1 >= self::JOIN_LIMIT)
            throw new Exception("Cannot exceed ". self::JOIN_LIMIT . " join statement on a query");

        $this->joins[] = new QueryJoin(
            $mode,
            $table,
            $alias,
            $source,
            $targetField,
            $joinOperator
        );
        return $this;
    }

    public function order(
        string $table,
        string $field,
        string $mode="ASC"
    ): self {
        $this->orders[] = new QueryOrder(
            new QueryField($table, $field),
            $mode
        );
        return $this;
    }

    protected function buildEssentials(): string
    {
        $essentials = "";

        $essentials .= count($this->conditions) ? "WHERE " . join(" AND \n", array_map(fn($x) => "$x", $this->conditions)): "";
        $essentials .= join("\n", array_map(fn($x) => "$x", $this->orders));
        $essentials .=  $this->limit ?
            "LIMIT $this->limit ". ($this->offset ? "OFFSET $this->offset" : ""):
            "";

        return $essentials;
    }

    protected function buildInsert(): string
    {
        return join(" ", [
            "INSERT INTO",
            $this->targetTable,
            "(".join(",", $this->insertFields).")",
            "VALUES",
            ...$this->insertValues
        ]);
    }

    protected function buildSelect(): string
    {
        return join(" ", [
            "SELECT",
            join(",\n", array_map(fn($x) => "$x", $this->fields)),
            "FROM `$this->targetTable`\n",
            join("\n", array_map(fn($x) => "$x", $this->joins)),

            $this->buildEssentials()
        ]);
    }

    protected function buildUpdate(): string
    {
        return join(" ", [
            "UPDATE `$this->targetTable`",
            count($this->updates) ? "SET ". join(",\n", array_map(fn($x) => "$x", $this->updates)): "",

            $this->buildEssentials()
        ]);
    }

    protected function buildDelete(): string
    {
        return join(" ", [
            "DELETE FROM `$this->targetTable`",

            $this->buildEssentials()
        ]);
    }

    public function build(): string
    {
        if (!$this->mode)
            throw new Exception("Unconfigured query mode ! Please use setMode() method before building");

        switch ($this->mode)
        {
            case self::INSERT: return $this->buildInsert();
            case self::SELECT: return $this->buildSelect();
            case self::UPDATE: return $this->buildUpdate();
            case self::DELETE: return $this->buildDelete();
            default : return "";
        }
    }

    public function first(): array|null
    {
        $res = $this->limit(1, 0)->fetch();
        return $res[0] ?? null;
    }

    public function fetch(Database $database=null): array
    {
        $database ??= Database::getInstance();
        $res = $database->query($this->build(), [], PDO::FETCH_NUM);

        $data = [];

        $lastTable = null;
        foreach ($res as $row)
        {
            $data[] = [];
            $lastId = count($data)-1;

            for ($i=0; $i<count($this->fields); $i++)
            {
                $field = $this->fields[$i];

                if ($lastTable != $field->table)
                {
                    $ref = &$data[$lastId];
                    $lastTable = $field->table;

                    foreach (explode("&", $field->table) as $c)
                    {
                        $ref[$c] ??= [];
                        $ref = &$ref[$c];
                    }
                    $ref["data"] ??= [];
                    $ref = &$ref["data"];
                }

                $ref[$field->field] = $row[$i];
            }

            $data[$lastId] = $data[$lastId][$this->targetTable];
        }

        return $data;
    }
}