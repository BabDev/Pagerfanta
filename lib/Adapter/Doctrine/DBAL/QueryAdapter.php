<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine DBAL QueryBuilder.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class QueryAdapter implements AdapterInterface
{
    private readonly QueryBuilder $queryBuilder;

    /**
     * @var callable(QueryBuilder): QueryBuilder
     */
    private $countQueryBuilderModifier;

    /**
     * @param callable(QueryBuilder): QueryBuilder $countQueryBuilderModifier
     */
    public function __construct(QueryBuilder $queryBuilder, callable $countQueryBuilderModifier)
    {
        $this->queryBuilder = clone $queryBuilder;
        $this->countQueryBuilderModifier = $countQueryBuilderModifier;
    }

    /**
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        $qb = $this->prepareCountQueryBuilder();

        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * @param int<0, max> $offset
     * @param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $qb = clone $this->queryBuilder;

        return $qb->setMaxResults($length)
            ->setFirstResult($offset)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function prepareCountQueryBuilder(): QueryBuilder
    {
        $qb = clone $this->queryBuilder;
        $callable = $this->countQueryBuilderModifier;

        return $callable($qb);
    }
}
