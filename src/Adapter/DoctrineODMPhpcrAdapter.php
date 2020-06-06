<?php

namespace Pagerfanta\Adapter;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;

/**
 * Pagerfanta adapter for Doctrine PHPCR-ODM.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class DoctrineODMPhpcrAdapter implements AdapterInterface
{
    private $queryBuilder;

    /**
     * Constructor.
     *
     * @param QueryBuilder $queryBuilder a Doctrine PHPCR-ODM query builder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Returns the query builder.
     *
     * @return QueryBuilder the query builder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function getNbResults()
    {
        return $this->queryBuilder->getQuery()->execute(null, Query::HYDRATE_PHPCR)->getRows()->count();
    }

    public function getSlice($offset, $length)
    {
        return $this->queryBuilder
            ->getQuery()
            ->setMaxResults($length)
            ->setFirstResult($offset)
            ->execute();
    }
}
