<?php

namespace r3pt1s\mysql\query;

use Closure;
use PDOException;
use r3pt1s\mysql\ConnectionPool;
use r3pt1s\mysql\util\Connection;
use PDOStatement;
use pmmp\thread\ThreadSafe;
use RuntimeException;
use Throwable;

abstract class MySQLQuery extends ThreadSafe {

    private mixed $result = null;
    private bool $crashed = false;
    private mixed $exception = null;
    private bool $resultSerialized = false;

    public function run(Connection $connection): void {
        try {
            $result = $this->onRun($connection);
            $this->setResult($result);
        } catch (Throwable $throwable) {
            $this->crashed = true;
            $this->exception = serialize(!$throwable instanceof PDOException ? $throwable : new RuntimeException($throwable->getMessage(), $throwable->getCode()));
        }
    }

    abstract public function onRun(Connection $connection): mixed;

    public function getResult(): mixed {
        if ($this->result === null) return null;
        return $this->resultSerialized ? igbinary_unserialize($this->result) : $this->result;
    }

    private function setResult(mixed $result): void {
        if ($result !== null && !$result instanceof PDOStatement && $this->isSerializable($result)) {
            $this->resultSerialized = !is_scalar($result) && !$result instanceof ThreadSafe;
            $this->result = $this->resultSerialized ? igbinary_serialize($result) : $result;
        }
    }

    public function execute(?Closure $syncClosure = null): void {
        ConnectionPool::getInstance()->addQuery($this, $syncClosure);
    }

    public function isCrashed(): bool {
        return $this->crashed;
    }

    public function getException(): mixed {
        return $this->crashed ? unserialize($this->exception) : null;
    }

    public function isSerializable(mixed $var): bool {
        if (is_resource($var)) {
            return false;
        } elseif (is_object($var)) {
            if ($var instanceof Closure) {
                return false;
            } elseif (!$var instanceof Serializable && !$var instanceof ArrayAccess) {
                return false;
            }
        }

        return true;
    }

    public static function create(mixed ...$args): self {
        $className = static::class;
        return new $className(...$args);
    }
}