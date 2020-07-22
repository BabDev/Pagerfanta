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

    public function getQueryBuilder(): QueryBuilder
    {
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
