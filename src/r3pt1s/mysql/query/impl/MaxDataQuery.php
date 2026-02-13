<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\util\ThreadedHelper;

class MaxDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ?ThreadSafeArray $join,
        private readonly ?string $column,
        private readonly ?ThreadSafeArray $where
    ) {}

    public function onRun(Connection $connection): ?string {
        return $connection->max(
            $this->table,
            $this->join !== null ? ThreadedHelper::toNormalArray($this->join) : null,
            $this->column,
            $this->where instanceof ThreadSafeArray ? ThreadedHelper::toNormalArray($this->where) : null
        );
    }
}