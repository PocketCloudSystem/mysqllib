<?php

namespace r3pt1s\mysql\query;

use r3pt1s\mysql\util\Connection;
use pmmp\thread\ThreadSafeArray;

class BatchQuery extends MySQLQuery {

    public function __construct(private readonly ThreadSafeArray $queries) {}

    /**
     * @param Connection $connection
     * @return array[]
     *
     * The batch query gives a different result than the other queries.
     * $batchQuery->execute()->onCompletion(
     *     function (array $results): void {
     *         [$results, $crashedQueries] = $results;
     *         foreach ($results as $i => $v) {
     *             if ($v === null && isset($crashedQueries[$i])) {
     *                 // query crashed
     *             }
     *         }
     *     },
     *    function (): void {
     *        // the batch query itself crashed
     *    }
     * )
     */
    public function onRun(Connection $connection): array {
        $results = [];
        $crashed = [];
        /** @var MySQLQuery $query */
        foreach ($this->queries as $i => $query) {
            $query->run($connection);
            $results[$i] = $query->isCrashed() ? null : $query->getResult();
            if ($query->isCrashed()) $crashed[$i] = $i;
        }

        return [$results, $crashed];
    }

    public static function create(mixed ...$queries): BatchQuery {
        return new self(ThreadSafeArray::fromArray($queries));
    }
}