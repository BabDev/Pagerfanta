<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine MongoDB ODM QueryBuilder.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class QueryAdapter implements AdapterInterface
{
    public function __construct(
        private readonly Builder $queryBuilder,
    ) {
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        $qb = clone $this->queryBuilder;

        return $qb->limit(0)
            ->skip(0)
            ->count()
            ->getQuery()
            ->execute();
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->queryBuilder->limit($length)
            ->skip($offset)
            ->getQuery()
            ->execute();
    }
}
