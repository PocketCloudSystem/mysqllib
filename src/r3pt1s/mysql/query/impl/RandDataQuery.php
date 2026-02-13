<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\util\ThreadedHelper;

class RandDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $join,
        private readonly ThreadSafeArray|string|null $columns,
        private readonly ?ThreadSafeArray $where
    ) {}

    public function onRun(Connection $connection): ?array {
        return $connection->rand(
            $this->table,
            ThreadedHelper::toNormalArray($this->join),
            $this->columns instanceof ThreadSafeArray ? ThreadedHelper::toNormalArray($this->join) : $this->columns,
            $this->where instanceof ThreadSafeArray ? ThreadedHelper::toNormalArray($this->where) : null
        );
    }
}