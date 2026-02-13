<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\util\ThreadedHelper;

class UpdateDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $data,
        private readonly ?ThreadSafeArray $where,
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->update(
            $this->table,
            ThreadedHelper::toNormalArray($this->data),
            $this->where !== null ? ThreadedHelper::toNormalArray($this->where) : null
        )?->errorCode() === "00000";
    }
}