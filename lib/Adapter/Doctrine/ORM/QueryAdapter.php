<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine ORM Query or QueryBuilder.
 *
 * @template T
 * @implements AdapterInterface<T>
 */
class QueryAdapter implements AdapterInterface
{
    /**
     * @var Paginator<T>
     */
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

    /**
     * @deprecated to be removed in 4.0
     */
    public function getQuery(): Query
    {
        trigger_deprecation('pagerfanta/pagerfanta', '3.2', 'Retrieving the %s from "%s" is deprecated and will be removed in 4.0.', Query::class, static::class);

        return $this->paginator->getQuery();
    }

    /**
     * @deprecated to be removed in 4.0
     */
    public function getFetchJoinCollection(): bool
    {
        trigger_deprecation('pagerfanta/pagerfanta', '3.2', 'Retrieving the fetchJoinCollection status from "%s" is deprecated and will be removed in 4.0.', static::class);

        return $this->paginator->getFetchJoinCollection();
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return \count($this->paginator);
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @phpstan-return \ArrayIterator<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $this->paginator->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($length);

        return $this->paginator->getIterator();
    }
}
