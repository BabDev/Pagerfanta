<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Cursor\Cursor;

/**
 * A cursor adapter that is always empty.
 *
 * @template-implements CursorAdapterInterface<never>
 */
class EmptyCursorAdapter implements CursorAdapterInterface, CountableAdapterInterface
{
    public function getNbResults(): int
    {
        return 0;
    }

    public function supportsBackwardNavigation(): bool
    {
        return false;
    }

    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        return new CursorSlice([]);
    }
}
