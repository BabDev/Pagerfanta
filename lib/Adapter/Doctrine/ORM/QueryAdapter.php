<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine ORM Query or QueryBuilder.
 */
class QueryAdapter implements AdapterInterface
{
    private Paginator $paginator;

    /**
     * @param Query|QueryBuilder $query
     * @param bool               $fetchJoinCollection Whether the query joins a collection (true by default)
     * @param bool|null          $useOutputWalkers    Flag indicating whether output walkers are used in the paginator
     */
    public function __construct($query, bool $fetchJoinCollection = true, ?bool $useOutputWalkers = null)
    {
        $this->paginator = new Paginator($query, $fetchJoinCollection);
        $this->paginator->setUseOutputWalkers($useOutputWalkers);
    }

    public function getQuery(): Query
    {
        return $this->paginator->getQuery();
    }

    public function getFetchJoinCollection(): bool
    {
        return $this->paginator->getFetchJoinCollection();
    }

    public function getNbResults(): int
    {
        return \count($this->paginator);
    }

    public function getSlice(int $offset, int $length): iterable
    {
        $this->paginator->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($length);

        return $this->paginator->getIterator();
    }
}
