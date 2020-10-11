<?php

namespace Pagerfanta\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\InvalidArgumentException;

/**
 * Adapter which calculates pagination from a Doctrine DBAL QueryBuilder.
 */
class QueryAdapter implements AdapterInterface
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var callable
     */
    private $countQueryBuilderModifier;

    /**
     * @param callable $countQueryBuilderModifier a callable to modify the query builder to count the results, the callable should have a signature of `function (QueryBuilder $queryBuilder): void {}`
     *
     * @throws InvalidArgumentException if a non-SELECT query is given or the modifier is not a callable
     */
    public function __construct(QueryBuilder $queryBuilder, $countQueryBuilderModifier)
    {
        if (QueryBuilder::SELECT !== $queryBuilder->getType()) {
            throw new InvalidArgumentException('Only SELECT queries can be paginated.');
        }

        if (!\is_callable($countQueryBuilderModifier)) {
            throw new InvalidArgumentException(sprintf('The $countQueryBuilderModifier argument of the %s constructor must be a callable, a %s was given.', self::class, \gettype($countQueryBuilderModifier)));
        }

        $this->queryBuilder = clone $queryBuilder;
        $this->countQueryBuilderModifier = $countQueryBuilderModifier;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        $qb = $this->prepareCountQueryBuilder();

        $stmt = $qb->execute();

        if (method_exists($stmt, 'fetchOne')) {
            return (int) $stmt->fetchOne();
        }

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        $qb = clone $this->queryBuilder;

        $stmt = $qb->setMaxResults($length)
            ->setFirstResult($offset)
            ->execute();

        if (method_exists($stmt, 'fetchAllAssociative')) {
            return $stmt->fetchAllAssociative();
        }

        return $stmt->fetchAll();
    }

    private function prepareCountQueryBuilder(): QueryBuilder
    {
        $qb = clone $this->queryBuilder;
        $callable = $this->countQueryBuilderModifier;

        $callable($qb);

        return $qb;
    }
}
