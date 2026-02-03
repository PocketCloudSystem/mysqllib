<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;

class GetDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $join,
        private readonly ThreadSafeArray|string|null $columns,
        private readonly ?ThreadSafeArray $where
    ) {}

    public function onRun(Connection $connection): ?array {
        return $connection->get(
            $this->table, iterator_to_array($this->join),
            $this->columns instanceof ThreadSafeArray ? iterator_to_array($this->columns) : $this->columns,
            $this->where instanceof ThreadSafeArray ? iterator_to_array($this->where) : null
        );
    }
}