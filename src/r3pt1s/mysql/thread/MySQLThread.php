<?php

namespace r3pt1s\mysql\thread;

use pocketcloud\cloud\thread\Thread;
use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperHandlerEntry;

final class MySQLThread extends Thread {

    private int $startedTime;
    private int $lastQueryTime = 0;
    private readonly SleeperHandlerEntry $sleeperHandlerEntry;
    /** @var ThreadSafeArray<MySQLQuery> */
    private ThreadSafeArray $queries;
    /** @var ThreadSafeArray<MySQLQuery> */
    private ThreadSafeArray $doneQueries;

    public function __construct(
        private readonly ThreadSafeArray $credentials,
        private readonly int $connectionTimeout = 28800
    ) {
        $this->startedTime = time();
        $this->queries = new ThreadSafeArray();
        $this->doneQueries = new ThreadSafeArray();
    }

    public function onRun(): void {
        $connection = new Connection(...$this->credentials);

        while (true) {
            $this->synchronized(function(): void {
                if ($this->isRunning() && $this->queries->count() == 0 && $this->doneQueries->count() == 0) $this->wait();
            });

            $subTime = $this->lastQueryTime == 0 ? $this->startedTime : $this->lastQueryTime;
            if ((time() - $subTime) >= $this->connectionTimeout) {
                $this->lastQueryTime = time();
                $connection = new Connection(...$this->credentials);
            }

            /** @var MySQLQuery $query */
            if (($query = $this->queries->shift()) !== null) {
                $this->lastQueryTime = time();
                $query->run($connection);
                $this->doneQueries[] = $query;
                $this->sleeperHandlerEntry->createNotifier()->wakeupSleeper();
            }
        }
    }

    public function setSleeperHandlerEntry(SleeperHandlerEntry $sleeperHandlerEntry): void {
        $this->sleeperHandlerEntry = $sleeperHandlerEntry;
    }

    public function addQuery(MySQLQuery $query): void {
        $this->synchronized(function() use($query): void {
            $this->queries[] = $query;
            $this->notify();
        });
    }

    public function getQueries(): ThreadSafeArray {
        return $this->queries;
    }

    public function getDoneQueries(): ThreadSafeArray {
        return $this->doneQueries;
    }

    public function getConnectionTimeout(): int {
        return $this->connectionTimeout;
    }
}