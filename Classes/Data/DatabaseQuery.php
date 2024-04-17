<?php

namespace Sharp\Classes\Data;

use Exception;
use InvalidArgumentException;
use PDO;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Data\Classes\QueryCondition;
use Sharp\Classes\Data\Classes\QueryField;
use Sharp\Classes\Data\Classes\QueryJoin;
use Sharp\Classes\Data\Classes\QueryOrder;
use Sharp\Classes\Data\Classes\QuerySet;
use Sharp\Classes\Data\Classes\QueryConditionRaw;
use Sharp\Classes\Data\Database;
use Sharp\Core\Utils;
use Sharp\Classes\Data\Model;

class DatabaseQuery
{
    use Configurable;

    const INSERT = 1;
    const CREATE = 1;

    const SELECT = 2;
    const READ   = 2;

    const UPDATE = 3;

    const DELETE = 4;

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

    public static function getDefaultConfiguration(): array
    {
        return ["join-limit" => 50];
    }

    public function __construct(string $table, int $mode)
    {
        $this->targetTable = $table;
        $this->setMode($mode);
        $this->loadConfiguration();
    }

    public function set(string $field, mixed $value, string $table=null): self
    {
        $this->updates[] = new QuerySet($field, $value, $table);
        return $this;
    }

    public function setInsertField(array $fields): self
    {
        $fields = ObjectArray::fromArray($fields);

        if ($fields->any(fn($field) => str_contains($field, "`")))
            throw new InvalidArgumentException("Fields with backticks are not yet supported");

        $this->insertFields = $fields->map(fn($field) => "`$field`" )->collect();
        return $this;
    }

    public function insertValues(array ...$setsOfValues): self
    {
        if (!count($this->insertFields))
            throw new Exception("Cannot insert values until insert fields are defined");

        foreach ($setsOfValues as $values)
        {
            if (count($values) !== count($this->insertFields))
                throw new Exception(sprintf("DatabaseQuery insert: %s values expected, %s given", count($this->insertFields), count($values)));

            $this->insertValues[] = Database::getInstance()->build("{}", [$values]);
        }

        return $this;
    }

    /**
     * Add a field to a SELECT query
     * @param string $table Table/Alias to select from
     * @param string $field Column/Field name
     * @param string $alias Alias for the selected column
     * @param int $type How is the field parsed (DatabaseField type constant)
     */
    public function addField(string $table, string $field, string $alias=null, int $type=DatabaseField::STRING): self
    {
        $this->fields[] = new QueryField($table, $field, $alias, $type);
        return $this;
    }

    public function exploreModel(string $model, bool $recursive=true, array $foreignKeyIgnores=[]): self
    {
        if (!Utils::uses($model, Model::class))
            throw new InvalidArgumentException("[$model] must use model trait");
        /** @var \Sharp\Classes\Data\Model $model */

        $references = [];

        $table = $model::getTable();
        $fields = $model::getFields();

        foreach ($fields as $_ => $field)
        {
            $this->addField($table, $field->name, null, $field->type);

            if (!$ref = $field->reference)
                continue;

            $references[] = [
                $table,
                $field->name,
                ...$ref,
                [$table]
            ];
        }

        if ($recursive)
            $this->exploreReferences($references, $foreignKeyIgnores);

        return $this;
    }

    protected function exploreReferences(array $references, array $foreignKeyIgnores=[]): void
    {
        $nextReferences = [];

        /** @var \Sharp\Classes\Data\Model $model */
        foreach ($references as [$origin, $field, $model, $target, $tableAcc])
        {
            $targetAcc = "$origin&$field";

            if (in_array($targetAcc, $foreignKeyIgnores))
                continue;

            $this->joins[] = new QueryJoin(
                "LEFT",
                new QueryField($origin, $field),
                "=",
                $model::getTable(),
                $targetAcc,
                $target
            );

            if (count($this->joins) == $this->configuration["join-limit"])
                return;

            foreach ($model::getFields() as $fieldName => $field)
            {
                $this->addField($targetAcc, $field->name, null, $field->type);

                if (!($ref = $field->reference))
                    continue;

                $nextTarget = $ref[0];

                if (in_array($nextTarget, $tableAcc))
                    continue;

                if (in_array("$targetAcc&$fieldName", $foreignKeyIgnores))
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
            $this->exploreReferences($nextReferences, $foreignKeyIgnores);
    }

    /**
     * Set the limit to a non-INSERT query
     * @param int $limit Limit to the query
     * @param int $offset Query offset (Optional)
     */
    public function limit(int $limit, int $offset=null): self
    {
        if ($limit < 0)
            $limit = 0;

        $this->limit = $limit;
        if ($offset)
            $this->offset($offset);
        return $this;
    }

    public function offset(int $offset): self
    {
        if ($offset < 0)
            $offset = 0;

        $this->offset = $offset;
        return $this;
    }

    protected function setMode(int $mode): self
    {
        if (!in_array($mode, [self::INSERT, self::SELECT, self::UPDATE, self::DELETE]))
            throw new InvalidArgumentException("Given mode must be a DatabaseQuery type constant !");

        $this->mode = $mode;
        return $this;
    }

    /**
     * Add a condition to the query (conditions are joined with 'AND')
     *
     * For raw SQL condition, see `whereSQL()`
     *
     * @param string $field Field name/alias to compare
     * @param mixed $value Value to compare to
     * @param string $operator Comparison operator
     * @param string $table (Optional) table specification for the compared field
     * @note A '= NULL' condition will be transformed to a `IS NULL`
     * @note A '<> NULL' condition will be transformed to a `IS NOT NULL`
     */
    public function where(string $field, mixed $value, string $operator="=", string $table=null) : self
    {
        if (!$table) // Prevent Ambiguous Fields
        {
            $fieldsObject = ObjectArray::fromArray($this->fields);
            if ($compatible = $fieldsObject->find(fn($f) => $f->field == $field))
                $table = $compatible->table;
        }

        $this->conditions[] = new QueryCondition(
            $field,
            $value,
            $operator,
            $table
        );
        return $this;
    }

    /**
     * Add a raw SQL condition to your query
     * @param string $condition Raw SQL Condition
     * @param array $context Context for condition building (see `Database::build()`)
     */
    public function whereSQL(string $condition, array $context=[]): self
    {
        $this->conditions[] = new QueryConditionRaw($condition, $context);
        return $this;
    }

    public function join(
        string $mode,
        QueryField $source,
        string $joinOperator,
        string $table,
        string $alias,
        string $targetField
    ): self {
        $joinLimit = $this->configuration["join-limit"];

        if (count($this->joins)+1 >= $joinLimit)
            throw new Exception("Cannot exceed $joinLimit join statement on a query");

        $this->joins[] = new QueryJoin(
            $mode,
            $source,
            $joinOperator,
            $table,
            $alias,
            $targetField
        );
        return $this;
    }

    public function order(string $table, string $field, string $mode="ASC"): self
    {
        $this->orders[] = new QueryOrder(
            new QueryField($table, $field),
            $mode
        );
        return $this;
    }

    protected function buildEssentials(): string
    {
        if ($this->offset && is_null($this->limit))
            Logger::getInstance()->warning(new Exception("DatabaseQuery: setting an offset without a limit does not have any effect on the query"));

        $essentials = "";

        $essentials .= count($this->conditions) ?
            " WHERE " . join(" AND \n", $this->conditions): "";

        $essentials .= count($this->orders) ?
            " ORDER BY ". join(",\n", $this->orders): '';

        $essentials .= $this->limit ?
            " LIMIT $this->limit ". ($this->offset ? "OFFSET $this->offset" : ""): "";

        return $essentials;
    }

    protected function buildInsert(): string
    {
        return join(" ", [
            "INSERT INTO",
            $this->targetTable,
            "(".join(",", $this->insertFields).")",
            "VALUES",
            join(",", $this->insertValues)
        ]);
    }

    protected function buildSelect(): string
    {
        return join(" ", [
            "SELECT",
            join(",\n", $this->fields),
            "FROM `$this->targetTable`\n",
            join("\n", $this->joins),

            $this->buildEssentials()
        ]);
    }

    protected function buildUpdate(): string
    {
        return join(" ", [
            "UPDATE `$this->targetTable`",
            count($this->updates) ?
                "SET ". join(",\n", $this->updates):
                "",

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
        if (!($mode = $this->mode ?? false))
            throw new Exception("Un-configured query mode ! Please provide a valid DatabaseQuery mode when building");

        switch ($mode)
        {
            case self::INSERT: return $this->buildInsert();
            case self::SELECT: return $this->buildSelect();
            case self::UPDATE: return $this->buildUpdate();
            case self::DELETE: return $this->buildDelete();
            default : throw new Exception("Unknown DatabaseQuery mode [$mode] !");
        }
    }

    public function first(): ?array
    {
        $oldLimit = $this->limit;
        $oldOffset = $this->offset;

        $res = $this->limit(1, 0)->fetch();

        $this->limit = $oldLimit;
        $this->offset = $oldOffset;

        return $res[0] ?? null;
    }

    /**
     * @return array|int Return selected rows if the query is a SELECT query, affected row count otherwise
     */
    public function fetch(Database $database=null): array|int
    {
        $database ??= Database::getInstance();
        $res = $database->query($this->build(), [], PDO::FETCH_NUM);

        if ($this->mode !== self::SELECT)
            return $database->getLastStatement()->rowCount();

        $data = [];

        foreach ($res as $row)
        {
            $data[] = [];
            $lastId = count($data)-1;
            $lastTable = null;

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

                $ref[$field->field] = $field->fromString($row[$i]);
            }

            $data[$lastId] = $data[$lastId][$this->targetTable];
        }

        return $data;
    }
}