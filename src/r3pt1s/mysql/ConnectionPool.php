<?php

namespace r3pt1s\mysql;

use Closure;
use Exception;
use LogicException;
use pmmp\thread\ThreadSafeArray;
use pocketcloud\cloud\util\promise\Promise;
use pocketmine\snooze\SleeperHandler;
use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\thread\MySQLThread;
use r3pt1s\mysql\util\Connection;

final class ConnectionPool {

    private static ?self $instance = null;
    protected array $completionHandlers = [];
    /** @var array<MySQLThread> */
    protected array $threads = [];

    protected ?Connection $syncConnection = null;
    protected int $syncStartedTime = 0;
    protected int $lastSyncQueryTime = 0;

    protected bool $syncQueries = false; // If set to false, queries will be executed in separate thread(s)

    /**
     * @param array $credentials e.g. ["address" => "127.0.0.1", "user" => "admin", "password" => "123", "database" => "player", "port" => 3306] <-- in this order
     * @param int $threadCount
     * @param SleeperHandler $sleeperHandler
     * @param Closure(MySQLQuery|null $query, Exception $exception): void $onException
     * @param int $connectionTimeout MySQL Connection timeout in seconds
     */
    public function __construct(
        private readonly array $credentials,
        private readonly int $threadCount,
        private readonly SleeperHandler $sleeperHandler,
        private readonly Closure $onException,
        private readonly int $connectionTimeout = 28800
    ) {
        self::$instance = $this;

        for ($i = 0; $i < $this->threadCount; $i++) {
            $thread = new MySQLThread(ThreadSafeArray::fromArray($this->credentials), $this->connectionTimeout);

            $sleeperHandlerEntry = $this->sleeperHandler->addNotifier(function () use ($thread, $i): void {
                try {
                    /** @var MySQLQuery $query */
                    while (($query = $thread->getDoneQueries()->shift()) !== null) {
                        $id = spl_object_id($query);
                        /** @var Promise $promise */
                        [$promise] = $this->completionHandlers[$id];

                        if ($query->isCrashed()) {
                            $promise->reject($query->getException());
                            ($this->onException)($query, new Exception($query->getException()));
                        } else {
                            $promise->resolve($query->getResult());
                        }

                        unset($this->completionHandlers[$id]);
                    }
                } catch (Exception $exception) {
                    ($this->onException)(null, $exception);
                }
            });

            $thread->setSleeperHandlerEntry($sleeperHandlerEntry);
            $thread->start();
            $this->threads[] = $thread;
        }
    }

    public function enableSyncQueries(bool $enabled = true): self {
        $this->syncQueries = $enabled;
        return $this;
    }

    public function addQuery(MySQLQuery $query, bool $sync = false): Promise {
        $promise = new Promise();
        if ($this->syncQueries || $sync) {
            if ($this->syncStartedTime == 0) $this->syncStartedTime = time();
            if ($this->syncConnection === null) {
                $this->syncConnection = new Connection(...$this->credentials);
            }

            $subTime = $this->lastSyncQueryTime == 0 ? $this->syncStartedTime : $this->lastSyncQueryTime;
            if ((time() - $subTime) >= $this->connectionTimeout) {
                $this->lastSyncQueryTime = time();
                $this->syncConnection = new Connection(...$this->credentials);
            }

            $query->run($this->syncConnection);
            if ($query->isCrashed()) {
                $promise->reject($query->getException());
                ($this->onException)($query, new Exception($query->getException()));
            } else {
                $promise->resolve($query->getResult());
            }

            return $promise;
        }

        $this->completionHandlers[spl_object_id($query)] = [$promise, $query];
        $this->selectThread()->addQuery($query);
        return $promise;
    }

    protected function selectThread(): MySQLThread {
        $threads = $this->threads;
        if (count($threads) == 0) throw new LogicException("Tried to select a thread for a mysql query but there are no threads running.");
        usort($threads, static fn(MySQLThread $a, MySQLThread $b) => $a->getQueries()->count() <=> $b->getQueries()->count());
        return $threads[0];
    }

    public function getThreadCount(): int {
        return $this->threadCount;
    }

    public function getSleeperHandler(): SleeperHandler {
        return $this->sleeperHandler;
    }

    public function getOnException(): Closure {
        return $this->onException;
    }

    public function getConnectionTimeout(): int {
        return $this->connectionTimeout;
    }

    public static function getInstance(): ?self {
        return self::$instance;
    }
}