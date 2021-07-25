<?php

namespace Pagerfanta\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine ORM Query or QueryBuilder.
 *
 * @template T
 */
class QueryAdapter implements AdapterInterface
{
    /**
     * @var Paginator<T>
     */
    private $paginator;

    /**
     * @param Query|QueryBuilder $query
     * @param bool               $fetchJoinCollection Whether the query joins a collection (true by default)
     * @param bool|null          $useOutputWalkers    Flag indicating whether output walkers are used in the paginator
     */
    public function __construct($query, $fetchJoinCollection = true, $useOutputWalkers = null)
    {
        $this->paginator = new Paginator($query, $fetchJoinCollection);
        $this->paginator->setUseOutputWalkers($useOutputWalkers);
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->paginator->getQuery();
    }

    /**
     * Returns whether the query joins a collection.
     *
     * @return bool
     */
    public function getFetchJoinCollection()
    {
        return $this->paginator->getFetchJoinCollection();
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return \count($this->paginator);
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     * @phpstan-return \ArrayIterator<array-key, T>
     */
    public function getSlice($offset, $length)
    {
        $this->paginator->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($length);

        return $this->paginator->getIterator();
    }
}
