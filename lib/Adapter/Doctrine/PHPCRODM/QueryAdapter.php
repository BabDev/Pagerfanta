<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\PHPCRODM;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine PHPCR ODM QueryBuilder.
 */
class QueryAdapter implements AdapterInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @deprecated to be removed in 4.0
     */
    public function getQueryBuilder(): QueryBuilder
    {
        trigger_deprecation('pagerfanta/pagerfanta', '3.2', 'Retrieving the %s from "%s" is deprecated and will be removed in 4.0.', QueryBuilder::class, static::class);

        return $this->queryBuilder;
    }

    public function getNbResults(): int
    {
        return $this->queryBuilder->getQuery()
            ->execute(null, Query::HYDRATE_PHPCR)
            ->getRows()
            ->count();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->queryBuilder->getQuery()
            ->setMaxResults($length)
            ->setFirstResult($offset)
            ->execute();
    }
}
