<?php

namespace r3pt1s\mysql\util;

use Exception;
use PDOStatement;

class Connection extends Medoo {

    public function __construct(
        private readonly string $user,
        private readonly string $password,
        private readonly string $database
    ) {
        parent::__construct(["type" => "mysql", "host" => "127.0.0.1", "database" => $this->database, "username" => $this->user, "password" => $this->password]);
    }

    public function exec(string $statement, array $map = [], callable $callback = null): ?PDOStatement {
        try {
            return parent::exec($statement, $map, $callback);
        } catch (Exception) {
            parent::__construct(["type" => "mysql", "host" => "127.0.0.1", "database" => $this->database, "username" => $this->user, "password" => $this->password]);
            return parent::exec($statement, $map, $callback);
        }
    }
}