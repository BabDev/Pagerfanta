<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\InvalidArgumentException;

/**
 * Adapter which calculates pagination from a Doctrine DBAL QueryBuilder.
 */
class QueryAdapter implements AdapterInterface
{
    private QueryBuilder $queryBuilder;

    /**
     * @var callable
     * @phpstan-var callable(QueryBuilder): void
     */
    private $countQueryBuilderModifier;

    /**
     * @phpstan-param callable(QueryBuilder): void $countQueryBuilderModifier
     *
     * @throws InvalidArgumentException if a non-SELECT query is given
     */
    public function __construct(QueryBuilder $queryBuilder, callable $countQueryBuilderModifier)
    {
        if (QueryBuilder::SELECT !== $queryBuilder->getType()) {
            throw new InvalidArgumentException('Only SELECT queries can be paginated.');
        }

        $this->queryBuilder = clone $queryBuilder;
        $this->countQueryBuilderModifier = $countQueryBuilderModifier;
    }

    public function getNbResults(): int
    {
        $qb = $this->prepareCountQueryBuilder();

        return (int) $qb->execute()->fetchOne();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        $qb = clone $this->queryBuilder;

        return $qb->setMaxResults($length)
            ->setFirstResult($offset)
            ->execute()
            ->fetchAllAssociative();
    }

    private function prepareCountQueryBuilder(): QueryBuilder
    {
        $qb = clone $this->queryBuilder;
        $callable = $this->countQueryBuilderModifier;

        $callable($qb);

        return $qb;
    }
}
