<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\util\ThreadedHelper;

class DeleteDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $where
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->delete(
            $this->table,
            ThreadedHelper::toNormalArray($this->where)
        )?->errorCode() === "00000";
    }
}