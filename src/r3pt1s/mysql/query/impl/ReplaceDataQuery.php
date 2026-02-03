<?php

namespace r3pt1s\mysql\query\impl;

use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;

class ReplaceDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $columns,
        private readonly ?ThreadSafeArray $where,
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->replace($this->table, iterator_to_array($this->columns), $this->where !== null ? iterator_to_array($this->where) : null)?->errorCode() === "00000";
    }
}