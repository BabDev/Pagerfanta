<?php

namespace Pagerfanta\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine MongoDB ODM QueryBuilder.
 */
class QueryAdapter implements AdapterInterface
{
    /**
     * @var Builder
     */
    private $queryBuilder;

    public function __construct(Builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return Builder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        $qb = clone $this->queryBuilder;

        return $qb->limit(0)
            ->skip(0)
            ->count()
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        return $this->queryBuilder->limit($length)
            ->skip($offset)
            ->getQuery()
            ->execute();
    }
}
