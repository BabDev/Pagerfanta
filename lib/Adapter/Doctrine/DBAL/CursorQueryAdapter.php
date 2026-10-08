<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Exception\LogicException;

/**
 * Adapter which calculates cursor (keyset) based pagination from a Doctrine DBAL QueryBuilder.
 *
 * The sort columns replace any ORDER BY clause on the query, and together they must uniquely identify each row (i.e. the
 * last column should be the primary key). The sort columns must be selected by the query and must not contain null values.
 * The cursor fields are keyed by the expression of each sort column.
 *
 * @template T of array<string, mixed>
 *
 * @implements CursorAdapterInterface<T>
 */
class CursorQueryAdapter implements CursorAdapterInterface
{
    private const PARAMETER_PREFIX = 'pagerfanta_cursor_';

    private readonly QueryBuilder $queryBuilder;

    /**
     * @var non-empty-list<SortColumn>
     */
    private readonly array $sortColumns;

    /**
     * @param list<SortColumn> $sortColumns The columns to sort the query by, in order of precedence
     *
     * @throws InvalidArgumentException if no sort columns are given or a sort column is given more than once
     */
    public function __construct(QueryBuilder $queryBuilder, array $sortColumns)
    {
        if ([] === $sortColumns) {
            throw new InvalidArgumentException('At least one sort column is required.');
        }

        $expressions = array_map(static fn (SortColumn $column): string => $column->expression, $sortColumns);

        if (\count($expressions) !== \count(array_unique($expressions))) {
            throw new InvalidArgumentException('Each sort column must only be given once.');
        }

        $this->queryBuilder = clone $queryBuilder;
        $this->sortColumns = array_values($sortColumns);
    }

    public function supportsBackwardNavigation(): bool
    {
        return true;
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorSlice<T>
     *
     * @throws InvalidCursorException if the cursor fields do not match the sort columns
     * @throws LogicException         if the query uses the parameter names reserved by this adapter, or a result row is missing a sort column or has a null value for one
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        $reverse = $cursor instanceof Cursor && Direction::Previous === $cursor->direction;

        $qb = clone $this->queryBuilder;

        foreach ($this->sortColumns as $index => $column) {
            $order = $reverse ? ('ASC' === $column->order ? 'DESC' : 'ASC') : $column->order;

            if (0 === $index) {
                $qb->orderBy($column->expression, $order);
            } else {
                $qb->addOrderBy($column->expression, $order);
            }
        }

        if ($cursor instanceof Cursor) {
            $this->applyCursor($qb, $cursor, $reverse);
        }

        /** @var list<T> $rows */
        $rows = $qb->setFirstResult(0)
            ->setMaxResults($limit + 1)
            ->executeQuery()
            ->fetchAllAssociative();

        return CursorSlice::fromLookahead($rows, $limit, $cursor, $this->createCursor(...));
    }

    /**
     * Adds the keyset condition for the cursor to the query.
     *
     * The condition is expanded to `(a > :a) OR (a = :a AND b > :b) OR ...` instead of using a row value comparison, as
     * row values are not supported by every platform and cannot mix sort orders.
     *
     * @throws InvalidCursorException if the cursor fields do not match the sort columns
     * @throws LogicException         if the query uses the parameter names reserved by this adapter
     */
    private function applyCursor(QueryBuilder $qb, Cursor $cursor, bool $reverse): void
    {
        $expressions = array_map(static fn (SortColumn $column): string => $column->expression, $this->sortColumns);

        if (\count($cursor->fields) !== \count($expressions) || [] !== array_diff($expressions, array_keys($cursor->fields))) {
            throw new InvalidCursorException(\sprintf('The cursor fields must match the sort columns "%s".', implode('", "', $expressions)));
        }

        $expr = $qb->expr();
        $conditions = [];
        $equalities = [];

        foreach ($this->sortColumns as $index => $column) {
            $value = $cursor->fields[$column->expression];

            if (null === $value) {
                throw new InvalidCursorException(\sprintf('The cursor value for the "%s" sort column must not be null.', $column->expression));
            }

            $parameter = self::PARAMETER_PREFIX.$index;

            if (\array_key_exists($parameter, $qb->getParameters())) {
                throw new LogicException(\sprintf('The query must not use the "%s" parameter, the "%s" prefix is reserved for the cursor parameters.', $parameter, self::PARAMETER_PREFIX));
            }

            $qb->setParameter($parameter, $value, match (true) {
                \is_int($value) => ParameterType::INTEGER,
                \is_bool($value) => ParameterType::BOOLEAN,
                default => ParameterType::STRING,
            });

            $operator = ('ASC' === $column->order) !== $reverse ? '>' : '<';
            $comparison = $expr->comparison($column->expression, $operator, ':'.$parameter);

            $conditions[] = [] === $equalities ? $comparison : $expr->and(...[...$equalities, $comparison]);
            $equalities[] = $expr->eq($column->expression, ':'.$parameter);
        }

        $qb->andWhere($expr->or(...$conditions));
    }

    /**
     * @param T $row
     *
     * @throws LogicException if the row is missing a sort column or has a non-scalar or null value for one
     */
    private function createCursor(array $row, Direction $direction): Cursor
    {
        $fields = [];

        foreach ($this->sortColumns as $column) {
            if (!\array_key_exists($column->resultKey, $row)) {
                throw new LogicException(\sprintf('The "%s" key for the "%s" sort column is missing from the result row, ensure the column is selected or set the result key of the sort column.', $column->resultKey, $column->expression));
            }

            $value = $row[$column->resultKey];

            if (null === $value || !\is_scalar($value)) {
                throw new LogicException(\sprintf('The "%s" sort column must have a scalar value in every result row, "%s" given.', $column->expression, get_debug_type($value)));
            }

            $fields[$column->expression] = $value;
        }

        return new Cursor($fields, $direction);
    }
}
