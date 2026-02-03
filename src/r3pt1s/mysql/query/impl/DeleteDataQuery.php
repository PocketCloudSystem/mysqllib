<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;

class DeleteDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $where
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->delete(
            $this->table,
            iterator_to_array($this->where)
        )?->errorCode() === "00000";
    }
}