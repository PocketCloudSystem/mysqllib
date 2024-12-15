<?php

namespace r3pt1s\mysql\thread;

use pocketcloud\cloud\thread\Thread;
use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperHandlerEntry;

final class MySQLThread extends Thread {

    private readonly SleeperHandlerEntry $sleeperHandlerEntry;
    /** @var ThreadSafeArray<MySQLQuery> */
    private ThreadSafeArray $queries;
    /** @var ThreadSafeArray<MySQLQuery> */
    private ThreadSafeArray $doneQueries;

    public function __construct(
        private readonly ThreadSafeArray $credentials
    ) {
        $this->queries = new ThreadSafeArray();
        $this->doneQueries = new ThreadSafeArray();
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

    public function onRun(): void {
        $connection = new Connection(...$this->credentials);

        while (true) {
            $this->synchronized(function(): void {
                if ($this->isRunning() && $this->queries->count() == 0 && $this->doneQueries->count() == 0) $this->wait();
            });

            /** @var MySQLQuery $query */
            if (($query = $this->queries->shift()) !== null) {
                $query->run($connection);
                $this->doneQueries[] = $query;
                $this->sleeperHandlerEntry->createNotifier()->wakeupSleeper();
            }
        }
    }
}