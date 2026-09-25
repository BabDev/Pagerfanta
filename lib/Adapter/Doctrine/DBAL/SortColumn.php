<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL;

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * A column used to sort, and paginate, a query with the {@see CursorQueryAdapter}.
 */
final class SortColumn
{
    /**
     * @var 'ASC'|'DESC'
     */
    public readonly string $order;

    /**
     * @var non-empty-string
     */
    public readonly string $resultKey;

    /**
     * @param non-empty-string      $expression The SQL expression for the column, including the table alias (e.g. "p.created_at"); this is added to the query as-is and must never contain user input
     * @param string                $order      The sort order, either "ASC" or "DESC"
     * @param non-empty-string|null $resultKey  The key of the column in each result row, defaults to the part of the expression after the last "."
     *
     * @throws InvalidArgumentException if the expression or result key is empty, or the order is not valid
     */
    public function __construct(
        public readonly string $expression,
        string $order = 'ASC',
        ?string $resultKey = null,
    ) {
        if ('' === $expression) {
            throw new InvalidArgumentException('The expression of a sort column must not be empty.');
        }

        $order = strtoupper($order);

        if ('ASC' !== $order && 'DESC' !== $order) {
            throw new InvalidArgumentException(\sprintf('The order of the "%s" sort column must be "ASC" or "DESC", "%s" given.', $expression, $order));
        }

        if (null === $resultKey) {
            $dot = strrpos($expression, '.');
            $resultKey = false === $dot ? $expression : substr($expression, $dot + 1);
        }

        if ('' === $resultKey) {
            throw new InvalidArgumentException(\sprintf('The result key of the "%s" sort column must not be empty.', $expression));
        }

        $this->order = $order;
        $this->resultKey = $resultKey;
    }
}
