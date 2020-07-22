<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine MongoDB ODM QueryBuilder.
 */
class QueryAdapter implements AdapterInterface
{
    private Builder $queryBuilder;

    public function __construct(Builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getQueryBuilder(): Builder
    {
        return $this->queryBuilder;
    }

    public function getNbResults(): int
    {
        $qb = clone $this->queryBuilder;

        return $qb->limit(0)
            ->skip(0)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->queryBuilder->limit($length)
            ->skip($offset)
            ->getQuery()
            ->execute();
    }
}
