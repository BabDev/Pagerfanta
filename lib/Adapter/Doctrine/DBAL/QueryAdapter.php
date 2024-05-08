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
     * @var callable
     *
     * @phpstan-var callable(QueryBuilder): (QueryBuilder|void)
     */
    private $countQueryBuilderModifier;

    /**
     * @phpstan-param callable(QueryBuilder): (QueryBuilder|void) $countQueryBuilderModifier
     */
    public function __construct(QueryBuilder $queryBuilder, callable $countQueryBuilderModifier)
    {
        $this->queryBuilder = clone $queryBuilder;
        $this->countQueryBuilderModifier = $countQueryBuilderModifier;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        $qb = $this->prepareCountQueryBuilder();

        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
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

        $newQb = $callable($qb);

        if ($newQb instanceof QueryBuilder) {
            return $newQb;
        }

        trigger_deprecation('pagerfanta/doctrine-dbal-adapter', '4.6', 'Not returning a "%s" from the query builder modifier in "%s" is deprecated. In 5.0, returning a query builder object will be required.');

        return $qb;
    }
}
