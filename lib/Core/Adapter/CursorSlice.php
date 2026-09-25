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
}
