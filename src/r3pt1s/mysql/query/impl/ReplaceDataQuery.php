<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\util\ThreadedHelper;

class ReplaceDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $columns,
        private readonly ?ThreadSafeArray $where,
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->replace(
            $this->table,
            ThreadedHelper::toNormalArray($this->columns),
            $this->where !== null ? ThreadedHelper::toNormalArray($this->where) : null
        )?->errorCode() === "00000";
    }
}