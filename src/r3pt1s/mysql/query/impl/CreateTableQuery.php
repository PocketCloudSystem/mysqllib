<?php

namespace r3pt1s\mysql\query\impl;

use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;

class CreateTableQuery extends MySQLQuery {

    public function __construct(
        private readonly string $name,
        private readonly ThreadSafeArray $columns
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->create($this->name, iterator_to_array($this->columns))?->errorCode() === "00000";
    }
}