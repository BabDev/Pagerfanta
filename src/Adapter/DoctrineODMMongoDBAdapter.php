<?php

namespace Pagerfanta\Adapter;

use Doctrine\ODM\MongoDB\Query\Builder;

/**
 * DoctrineODMMongoDBAdapter.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class DoctrineODMMongoDBAdapter implements AdapterInterface
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

    public function getNbResults()
    {
        $qb = clone $this->queryBuilder;

        return $qb
            ->limit(0)
            ->skip(0)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function getSlice($offset, $length)
    {
        return $this->queryBuilder
            ->limit($length)
            ->skip($offset)
            ->getQuery()
            ->execute();
    }
}
