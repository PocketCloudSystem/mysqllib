<?php

namespace r3pt1s\mysql\util;

class Connection extends Medoo {

    public function __construct(
        private readonly string $address,
        private readonly string $user,
        private readonly string $password,
        private readonly string $database,
        private readonly int $port = 3306
    ) {
        parent::__construct(["type" => "mysql", "host" => $this->address, "database" => $this->database, "username" => $this->user, "password" => $this->password, "port" => $this->port]);
    }
}