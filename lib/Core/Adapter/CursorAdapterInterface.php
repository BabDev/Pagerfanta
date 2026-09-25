<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Exception\InvalidCursorException;

/**
 * An adapter supporting cursor (keyset) based pagination.
 *
 * @template-covariant T
 */
interface CursorAdapterInterface
{
    /**
     * Returns the slice of results following (or preceding, based on the cursor's direction) the given cursor.
     *
     * Implementations should fetch one more item than the limit to detect whether another page exists, trimming the extra
     * item before returning the slice. The items in the slice must always be in the list's sort order, regardless of the
     * direction of the cursor.
     *
     * @param Cursor|null  $cursor The cursor to paginate from, or null for the first page
     * @param positive-int $limit  The maximum number of items to return
     *
     * @return CursorSlice<T>
     *
     * @throws InvalidCursorException if the cursor is not valid for this adapter
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice;

    /**
     * Whether this adapter can paginate backwards (i.e. generate a cursor for the previous page).
     */
    public function supportsBackwardNavigation(): bool;
}
