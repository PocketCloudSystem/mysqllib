<?php

namespace r3pt1s\mysql\query\impl;

use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\util\ThreadedHelper;

class InsertDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $values,
        private readonly ?string $primaryKey = null
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->insert(
            $this->table,
            ThreadedHelper::toNormalArray($this->values),
            $this->primaryKey
        )?->errorCode() === "00000";
    }
}