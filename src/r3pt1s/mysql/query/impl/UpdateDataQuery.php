<?php

namespace r3pt1s\mysql\query\impl;

use pmmp\thread\ThreadSafeArray;
use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;

class UpdateDataQuery extends MySQLQuery {

    public function __construct(
        private readonly string $table,
        private readonly ThreadSafeArray $data,
        private readonly ?ThreadSafeArray $where,
    ) {}

    public function onRun(Connection $connection): bool {
        return $connection->update($this->table, iterator_to_array($this->data), $this->where !== null ? iterator_to_array($this->where) : null)?->errorCode() === "00000";
    }
}