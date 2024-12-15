<?php

namespace r3pt1s\mysql\query;

use Closure;
use r3pt1s\mysql\util\Connection;

class CustomQuery extends MySQLQuery {

    public function __construct(private readonly Closure $closure) {}

    public function onRun(Connection $connection): mixed {
        return ($this->closure)($connection);
    }

    public static function custom(Closure $closure): self {
        return new self($closure);
    }
}