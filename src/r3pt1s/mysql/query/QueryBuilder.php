<?php

namespace r3pt1s\mysql\query;

use Closure;
use r3pt1s\mysql\util\Connection;

final class QueryBuilder {

    /** @var array<MySQLQuery> */
    private array $queries = [];
    
    public function __construct(private string $table) {}

    public function changeTable(string $table): self {
        $this->table = $table;
        return $this;
    }

    public function execute(?Closure $syncClosure = null): void {
        if (empty($this->queries)) return;
        if (count($this->queries) > 1) {
            MultipleQueries::create(
                ...$this->queries
            )->execute($syncClosure);
        } else $this->queries[0]->execute($syncClosure);
    }

    public function create(array $columns): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->create($table, $columns));
        return $this;
    }

    public function drop(): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->drop($table));
        return $this;
    }

    public function select(array $join, array|string|null $columns = null, $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->select($table, $join, $columns, $where));
        return $this;
    }

    public function insert(array $values, ?string $primaryKey = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->insert($table, $values, $primaryKey));
        return $this;
    }

    public function update(array $data, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->update($table, $data, $where));
        return $this;
    }

    public function delete(array $where): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->delete($table, $where));
        return $this;
    }

    public function replace(array $columns, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->replace($table, $columns, $where));
        return $this;
    }

    public function get(?array $join = null, array|string|null $columns = null, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->get($table, $join, $columns, $where));
        return $this;
    }

    public function has(array $join, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->has($table, $join, $where));
        return $this;
    }

    public function rand(?array $join = null, array|string|null $columns = null, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->rand($table, $join, $columns, $where));
        return $this;
    }

    public function count(?array $join = null, ?string $column = null, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->count($table, $join, $column, $where));
        return $this;
    }

    public function min(?array $join = null, ?string $column = null, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->min($table, $join, $column, $where));
        return $this;
    }

    public function avg(?array $join = null, ?string $column = null, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->avg($table, $join, $column, $where));
        return $this;
    }

    public function max(?array $join = null, ?string $column = null, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->max($table, $join, $column, $where));
        return $this;
    }

    public function sum(?array $join = null, ?string $column = null, ?array $where = null): self {
        $table = $this->table;
        $this->queries[] = CustomQuery::custom(static fn(Connection $connection) => $connection->sum($table, $join, $column, $where));
        return $this;
    }

    public static function table(string $table): self {
        return new self($table);
    }
}