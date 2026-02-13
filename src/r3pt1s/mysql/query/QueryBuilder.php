<?php

namespace r3pt1s\mysql\query;

use pocketcloud\cloud\util\promise\Promise;
use r3pt1s\mysql\query\impl\AverageDataQuery;
use r3pt1s\mysql\query\impl\CountDataQuery;
use r3pt1s\mysql\query\impl\CreateTableQuery;
use r3pt1s\mysql\query\impl\DeleteDataQuery;
use r3pt1s\mysql\query\impl\DropTableQuery;
use r3pt1s\mysql\query\impl\GetDataQuery;
use r3pt1s\mysql\query\impl\HasDataQuery;
use r3pt1s\mysql\query\impl\InsertDataQuery;
use r3pt1s\mysql\query\impl\MaxDataQuery;
use r3pt1s\mysql\query\impl\MinDataQuery;
use r3pt1s\mysql\query\impl\RandDataQuery;
use r3pt1s\mysql\query\impl\ReplaceDataQuery;
use r3pt1s\mysql\query\impl\SelectDataQuery;
use r3pt1s\mysql\query\impl\SumDataQuery;
use r3pt1s\mysql\query\impl\UpdateDataQuery;
use r3pt1s\mysql\util\ThreadedHelper;

final class QueryBuilder {

    /** @var array<MySQLQuery> */
    private array $queries = [];
    
    public function __construct(private string $table) {}

    public function changeTable(string $table): self {
        $this->table = $table;
        return $this;
    }

    public function execute(): Promise {
        if (empty($this->queries)) return Promise::resolved();
        if (count($this->queries) > 1) {
            return BatchQuery::create(
                ...$this->queries
            )->execute();
        }

        return $this->queries[0]->execute();
    }

    public function create(array $columns): self {
        $this->queries[] = new CreateTableQuery($this->table, ThreadedHelper::toThreadSafeArray($columns));
        return $this;
    }

    public function drop(): self {
        $this->queries[] = new DropTableQuery($this->table);
        return $this;
    }

    public function select(array $join, array|string|null $columns = null, ?array $where = null): self {
        $this->queries[] = new SelectDataQuery($this->table, ThreadedHelper::toThreadSafeArray($join), is_array($columns) ? ThreadedHelper::toThreadSafeArray($columns) : $columns, is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function insert(array $values, ?string $primaryKey = null): self {
        $this->queries[] = new InsertDataQuery($this->table, ThreadedHelper::toThreadSafeArray($values), $primaryKey);
        return $this;
    }

    public function update(array $data, ?array $where = null): self {
        $this->queries[] = new UpdateDataQuery($this->table, ThreadedHelper::toThreadSafeArray($data), is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function delete(array $where): self {
        $this->queries[] = new DeleteDataQuery($this->table, ThreadedHelper::toThreadSafeArray($where));
        return $this;
    }

    public function replace(array $columns, ?array $where = null): self {
        $this->queries[] = new ReplaceDataQuery($this->table, ThreadedHelper::toThreadSafeArray($columns), is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function get(?array $join = null, array|string|null $columns = null, ?array $where = null): self {
        $this->queries[] = new GetDataQuery($this->table, is_array($join) ? ThreadedHelper::toThreadSafeArray($join) : $join, is_array($columns) ? ThreadedHelper::toThreadSafeArray($columns) : $columns, is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function has(array $join, ?array $where = null): self {
        $this->queries[] = new HasDataQuery($this->table, ThreadedHelper::toThreadSafeArray($join), is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function rand(?array $join = null, array|string|null $columns = null, ?array $where = null): self {
        $this->queries[] = new RandDataQuery($this->table, is_array($join) ? ThreadedHelper::toThreadSafeArray($join) : $join, is_array($columns) ? ThreadedHelper::toThreadSafeArray($columns) : $columns, is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function count(?array $join = null, ?string $column = null, ?array $where = null): self {
        $this->queries[] = new CountDataQuery($this->table, is_array($join) ? ThreadedHelper::toThreadSafeArray($join) : $join, $column, is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function min(?array $join = null, ?string $column = null, ?array $where = null): self {
        $this->queries[] = new MinDataQuery($this->table, is_array($join) ? ThreadedHelper::toThreadSafeArray($join) : $join, $column, is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function avg(?array $join = null, ?string $column = null, ?array $where = null): self {
        $this->queries[] = new AverageDataQuery($this->table, is_array($join) ? ThreadedHelper::toThreadSafeArray($join) : $join, $column, is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function max(?array $join = null, ?string $column = null, ?array $where = null): self {
        $this->queries[] = new MaxDataQuery($this->table, is_array($join) ? ThreadedHelper::toThreadSafeArray($join) : $join, $column, is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public function sum(?array $join = null, ?string $column = null, ?array $where = null): self {
        $this->queries[] = new SumDataQuery($this->table, is_array($join) ? ThreadedHelper::toThreadSafeArray($join) : $join, $column, is_array($where) ? ThreadedHelper::toThreadSafeArray($where) : $where);
        return $this;
    }

    public static function table(string $table): self {
        return new self($table);
    }
}