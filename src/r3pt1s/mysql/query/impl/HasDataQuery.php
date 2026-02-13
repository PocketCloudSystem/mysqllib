<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\util\ThreadedHelper;

class HasDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $join,
        private readonly ?ThreadSafeArray $where
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->has(
            $this->table,
            ThreadedHelper::toNormalArray($this->join),
            $this->where !== null ? ThreadedHelper::toNormalArray($this->where) : null
        );
    }
}