<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidArgumentException;

/**
 * A page of results from a cursor adapter, with the cursors pointing to its neighboring pages.
 *
 * @template-covariant T
 */
final class CursorSlice
{
    /**
     * @param list<T>     $items    The items on the page, in the list's sort order
     * @param Cursor|null $previous The cursor for the previous page, or null when there is no previous page (or backward navigation is not supported)
     * @param Cursor|null $next     The cursor for the next page, or null when there is no next page
     *
     * @throws InvalidArgumentException if a cursor's direction does not match its role
     */
    public function __construct(
        public readonly array $items,
        public readonly ?Cursor $previous = null,
        public readonly ?Cursor $next = null,
    ) {
        if ($previous instanceof Cursor && Direction::Previous !== $previous->direction) {
            throw new InvalidArgumentException('The previous cursor of a slice must have the previous direction.');
        }

        if ($next instanceof Cursor && Direction::Next !== $next->direction) {
            throw new InvalidArgumentException('The next cursor of a slice must have the next direction.');
        }
    }

    /**
     * Creates a slice from items fetched with one item of lookahead.
     *
     * @template TItem
     *
     * @param list<TItem>                        $items         The items fetched in the direction of the cursor, up to `$limit + 1` items
     * @param positive-int                       $limit         The maximum number of items on the page
     * @param Cursor|null                        $cursor        The cursor the items were fetched for, or null for the first page
     * @param callable(TItem, Direction): Cursor $cursorFactory Creates the cursor pointing to an item
     *
     * @return self<TItem>
     */
    public static function fromLookahead(array $items, int $limit, ?Cursor $cursor, callable $cursorFactory): self
    {
        $reverse = $cursor instanceof Cursor && Direction::Previous === $cursor->direction;
        $hasMore = \count($items) > $limit;

        $items = \array_slice($items, 0, $limit);

        if ($reverse) {
            $items = array_reverse($items);
        }

        if ([] === $items) {
            return new self([]);
        }

        $hasPrevious = $reverse ? $hasMore : $cursor instanceof Cursor;
        $hasNext = $reverse || $hasMore;

        return new self(
            $items,
            $hasPrevious ? $cursorFactory($items[0], Direction::Previous) : null,
            $hasNext ? $cursorFactory($items[array_key_last($items)], Direction::Next) : null,
        );
    }
}
