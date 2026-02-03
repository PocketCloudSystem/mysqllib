<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;

class AverageDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ?ThreadSafeArray $join,
        private readonly ?string $column,
        private readonly ?ThreadSafeArray $where
    ) {}

    public function onRun(Connection $connection): ?string {
        return $connection->avg(
            $this->table,
            $this->join !== null ? iterator_to_array($this->join) : null,
            $this->column,
            $this->where instanceof ThreadSafeArray ? iterator_to_array($this->where) : null
        );
    }
}