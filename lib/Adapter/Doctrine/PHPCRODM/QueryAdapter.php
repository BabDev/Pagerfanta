<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\PHPCRODM;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use Pagerfanta\Adapter\AdapterInterface;
use PHPCR\Query\QueryResultInterface;

/**
 * Adapter which calculates pagination from a Doctrine PHPCR ODM QueryBuilder.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class QueryAdapter implements AdapterInterface
{
    public function __construct(
        private readonly QueryBuilder $queryBuilder
    ) {}

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->queryBuilder->getQuery()
            ->execute(null, Query::HYDRATE_PHPCR)
            ->getRows()
            ->count();
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->queryBuilder->getQuery()
            ->setMaxResults($length)
            ->setFirstResult($offset)
            ->execute();
    }
}
