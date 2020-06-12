<?php

namespace Pagerfanta\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Exception\InvalidArgumentException;

/**
 * Extended Doctrine DBAL adapter which assists in building the count query modifier for a SELECT query on a single table.
 */
class SingleTableQueryAdapter extends QueryAdapter
{
    /**
     * @param string $countField Primary key for the table in query, used in the count expression. Must include table alias.
     *
     * @throws InvalidArgumentException if the query has JOIN statements or the count field does not have a table alias
     */
    public function __construct(QueryBuilder $queryBuilder, $countField)
    {
        if ($this->hasQueryBuilderJoins($queryBuilder)) {
            throw new InvalidArgumentException('The query builder cannot have joins.');
        }

        parent::__construct($queryBuilder, $this->createCountQueryModifier($countField));
    }

    private function hasQueryBuilderJoins(QueryBuilder $queryBuilder): bool
    {
        return !empty($queryBuilder->getQueryPart('join'));
    }

    /**
     * @param string $countField
     */
    private function createCountQueryModifier($countField): \Closure
    {
        $select = $this->createSelectForCountField($countField);

        return function (QueryBuilder $queryBuilder) use ($select): void {
            $queryBuilder->select($select)
                ->resetQueryPart('orderBy')
                ->setMaxResults(1);
        };
    }

    /**
     * @param string $countField
     *
     * @throws InvalidArgumentException if the count field does not have a table alias
     */
    private function createSelectForCountField($countField): string
    {
        if ($this->countFieldHasNoAlias($countField)) {
            throw new InvalidArgumentException('The $countField must contain a table alias in the string.');
        }

        return sprintf('COUNT(DISTINCT %s) AS total_results', $countField);
    }

    /**
     * @param string $countField
     */
    private function countFieldHasNoAlias($countField): bool
    {
        return false === strpos($countField, '.');
    }
}
