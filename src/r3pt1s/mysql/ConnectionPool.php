<?php

namespace r3pt1s\mysql;

use Closure;
use Exception;
use pmmp\thread\Thread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperHandler;
use r3pt1s\mysql\query\MySQLQuery;
use r3pt1s\mysql\thread\MySQLThread;

final class ConnectionPool {

    private static ?self $instance = null;
    protected array $completionHandlers = [];
    /** @var array<MySQLThread> */
    protected array $threads = [];

    /**
     * @param array $credentials e.g. ["address" => "127.0.0.1", "user" => "admin", "password" => "123", "database" => "player", "port" => 3306] <-- in this order
     * @param int $threadCount
     * @param SleeperHandler $sleeperHandler
     * @param Closure $onException
     */
    public function __construct(
        array $credentials,
        private readonly int $threadCount,
        private readonly SleeperHandler $sleeperHandler,
        private readonly Closure $onException
    ) {
        self::$instance = $this;

        for ($i = 0; $i < $this->threadCount; $i++) {
            $thread = new MySQLThread(ThreadSafeArray::fromArray($credentials));

            $sleeperHandlerEntry = $this->sleeperHandler->addNotifier(function () use ($thread, $i): void {
                try {
                    /** @var MySQLQuery $query */
                    while (($query = $thread->getDoneQueries()->shift()) !== null) {
                        if ($query->isCrashed()) {
                            ($this->onException)(new Exception($query->getException()));
                            return;
                        }

                        $id = spl_object_id($query);
                        [$successHandler] = $this->completionHandlers[$id];
                        if ($successHandler !== null) ($successHandler)($query->getResult());
                        unset($this->completionHandlers[$id]);
                    }
                } catch (Exception $exception) {
                    ($this->onException)($exception);
                }
            });

            $thread->setSleeperHandlerEntry($sleeperHandlerEntry);
            $thread->start(Thread::INHERIT_NONE);
            $this->threads[] = $thread;
        }
    }

    public function addQuery(MySQLQuery $query, ?Closure $syncClosure): void {
        $this->completionHandlers[spl_object_id($query)] = [$syncClosure, $query];
        $this->selectThread()->addQuery($query);
    }

    protected function selectThread(): MySQLThread {
        $threads = $this->threads;
        usort($threads, static fn(MySQLThread $a, MySQLThread $b) => $a->getQueries()->count() <=> $b->getQueries()->count());
        return $threads[0];
    }

    public function getThreadCount(): int {
        return $this->threadCount;
    }

    public function getThreads(): array {
        return $this->threads;
    }

    public static function getInstance(): ?self {
        return self::$instance;
    }
}