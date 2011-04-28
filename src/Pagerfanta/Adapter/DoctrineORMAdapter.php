<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta\Adapter;

use Doctrine\ORM\QueryBuilder;

/**
 * DoctrineORMAdapter.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class DoctrineORMAdapter implements AdapterInterface
{
    private $queryBuilder;
    private $results;

    /**
     * Constructor.
     *
     * @param QueryBuilder $queryBuilder A Doctrine ORM query builder.
     *
     * @api
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Returns the query builder.
     *
     * @return QueryBuilder The query builder.
     *
     * @api
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return count($this->getResults());
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        return array_slice($this->getResults(), $offset, $length);
    }

    private function getResults()
    {
        if (null === $this->results) {
            $this->results = $this->queryBuilder->getQuery()->getResult();
        }

        return $this->results;
    }
}
