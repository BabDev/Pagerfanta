<?php declare(strict_types=1);

namespace Pagerfanta\Position;

use Pagerfanta\Cursor\Cursor;

/**
 * A position within a cursor paginated list, identified by the cursor pointing to it.
 */
final class CursorPosition implements Position
{
    public function __construct(
        public readonly Cursor $cursor,
    ) {}
}
