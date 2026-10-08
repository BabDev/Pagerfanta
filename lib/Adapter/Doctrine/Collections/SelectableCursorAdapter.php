<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\Order;
use Doctrine\Common\Collections\Selectable;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Exception\LogicException;

/**
 * Adapter which calculates cursor (keyset) based pagination from a Selectable instance.
 *
 * The sort fields replace any orderings on the criteria, and together they must uniquely identify each item (i.e. the last
 * field should be the identifier). The sort fields must have scalar, non-null values. The cursor fields are keyed by the
 * name of each sort field.
 *
 * @template TKey of array-key
 * @template T
 *
 * @implements CursorAdapterInterface<T>
 */
class SelectableCursorAdapter implements CursorAdapterInterface
{
    private static ?bool $supportsSortDirection = null;

    /**
     * @var non-empty-array<string, 'ASC'|'DESC'>
     */
    private readonly array $sortFields;

    /**
     * @param Selectable<TKey, T>                            $selectable
     * @param array<string, 'ASC'|'DESC'|'asc'|'desc'|Order|\SortDirection> $sortFields The fields to sort the items by, in order of precedence, mapped to their sort order
     *
     * @throws InvalidArgumentException if no sort fields are given or a sort order is not valid
     */
    public function __construct(
        private readonly Selectable $selectable,
        private readonly Criteria $criteria,
        array $sortFields,
    ) {
        if ([] === $sortFields) {
            throw new InvalidArgumentException('At least one sort field is required.');
        }

        $normalized = [];

        foreach ($sortFields as $field => $order) {
            $order = match (true) {
                $order instanceof \SortDirection => \SortDirection::Ascending === $order ? 'ASC' : 'DESC',
                $order instanceof Order => $order->value,
                default => strtoupper($order),
            };

            if ('ASC' !== $order && 'DESC' !== $order) {
                throw new InvalidArgumentException(\sprintf('The order of the "%s" sort field must be "ASC" or "DESC", "%s" given.', $field, $order));
            }

            $normalized[$field] = $order;
        }

        $this->sortFields = $normalized;
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
     * @throws InvalidCursorException if the cursor fields do not match the sort fields
     * @throws LogicException         if an item has a non-scalar or null value for a sort field
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        $reverse = $cursor instanceof Cursor && Direction::Previous === $cursor->direction;

        $criteria = clone $this->criteria;

        // The accepted ordering values depend on the installed doctrine/collections version
        // @phpstan-ignore-next-line argument.type
        $criteria->orderBy($this->createOrderings($reverse));
        $criteria->setFirstResult(0);
        $criteria->setMaxResults($limit + 1);

        if ($cursor instanceof Cursor) {
            $criteria->andWhere($this->createKeysetExpression($cursor, $reverse));
        }

        return CursorSlice::fromLookahead(array_values($this->selectable->matching($criteria)->toArray()), $limit, $cursor, $this->createCursor(...));
    }

    /**
     * @return array<string, 'ASC'|'DESC'>|array<string, Order>|array<string, \SortDirection>
     */
    private function createOrderings(bool $reverse): array
    {
        $orderings = [];

        foreach ($this->sortFields as $field => $order) {
            if ($reverse) {
                $order = 'ASC' === $order ? 'DESC' : 'ASC';
            }

            $orderings[$field] = $order;
        }

        if ($this->supportsSortDirection()) {
            return array_map(static fn (string $order): \SortDirection => 'ASC' === $order ? \SortDirection::Ascending : \SortDirection::Descending, $orderings);
        }

        // The Order enum was added in doctrine/collections 2.2, and passing strings is deprecated since then
        if (enum_exists(Order::class)) {
            return array_map(static fn (string $order): Order => Order::from($order), $orderings);
        }

        return $orderings;
    }

    /**
     * Checks whether the criteria support the native SortDirection enum, which replaces the Order enum in doctrine/collections 3.1.
     *
     * The enum may be provided by a polyfill with an older doctrine/collections version which does not support it, so the
     * version is detected with the Criteria::getOrderings() method, which was removed in 3.0 and restored with a return type
     * in 3.1.
     */
    private function supportsSortDirection(): bool
    {
        return self::$supportsSortDirection ??= enum_exists(\SortDirection::class)
            // @phpstan-ignore-next-line function.alreadyNarrowedType
            && method_exists(Criteria::class, 'getOrderings')
            && (new \ReflectionMethod(Criteria::class, 'getOrderings'))->hasReturnType();
    }

    /**
     * Creates the keyset expression for the cursor, expanded to `(a > :a) OR (a = :a AND b > :b) OR ...`.
     *
     * @throws InvalidCursorException if the cursor fields do not match the sort fields
     */
    private function createKeysetExpression(Cursor $cursor, bool $reverse): Expression
    {
        if (\count($cursor->fields) !== \count($this->sortFields) || [] !== array_diff_key($this->sortFields, $cursor->fields)) {
            throw new InvalidCursorException(\sprintf('The cursor fields must match the sort fields "%s".', implode('", "', array_keys($this->sortFields))));
        }

        $expr = Criteria::expr();
        $conditions = [];
        $equalities = [];

        foreach ($this->sortFields as $field => $order) {
            $value = $cursor->fields[$field];

            if (null === $value) {
                throw new InvalidCursorException(\sprintf('The cursor value for the "%s" sort field must not be null.', $field));
            }

            $comparison = ('ASC' === $order) !== $reverse ? $expr->gt($field, $value) : $expr->lt($field, $value);

            $conditions[] = [] === $equalities ? $comparison : $expr->andX(...[...$equalities, $comparison]);
            $equalities[] = $expr->eq($field, $value);
        }

        return 1 === \count($conditions) ? $conditions[0] : $expr->orX(...$conditions);
    }

    /**
     * @param T $item
     *
     * @throws LogicException if the item has a non-scalar or null value for a sort field
     */
    private function createCursor(mixed $item, Direction $direction): Cursor
    {
        if (!\is_array($item) && !\is_object($item)) {
            throw new LogicException(\sprintf('The items must be arrays or objects to read their sort fields, "%s" given.', get_debug_type($item)));
        }

        // Read the values the same way the criteria are matched, raw field access is optional in doctrine/collections 2.x and always used in 3.0
        // @phpstan-ignore-next-line function.alreadyNarrowedType
        $rawFieldAccessFlag = method_exists($this->criteria, 'isRawFieldValueAccessEnabled') ? [$this->criteria->isRawFieldValueAccessEnabled()] : [];

        $fields = [];

        foreach (array_keys($this->sortFields) as $field) {
            $value = ClosureExpressionVisitor::getObjectFieldValue($item, $field, ...$rawFieldAccessFlag);

            if (null === $value || !\is_scalar($value)) {
                throw new LogicException(\sprintf('The "%s" sort field must have a scalar value for every item, "%s" given.', $field, get_debug_type($value)));
            }

            $fields[$field] = $value;
        }

        return new Cursor($fields, $direction);
    }
}
