<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;

class HasDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $join,
        private readonly ?ThreadSafeArray $where
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->has(
            $this->table,
            iterator_to_array($this->join),
            $this->where !== null ? iterator_to_array($this->where) : null
        );
    }
}