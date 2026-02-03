<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;

class DropTableQuery extends MySQLQuery {

    public function __construct(private readonly string $name) {}

    public function onRun(Connection $connection): bool {
        return $connection->drop($this->name)?->errorCode() === "00000";
    }
}