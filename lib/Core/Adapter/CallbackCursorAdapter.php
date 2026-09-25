<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Exception\LogicException;

/**
 * Adapter which calculates cursor based pagination from a callable function.
 *
 * @template T
 *
 * @implements CursorAdapterInterface<T>
 */
class CallbackCursorAdapter implements CursorAdapterInterface
{
    /**
     * @var callable(Cursor|null, positive-int): CursorSlice<T>
     */
    private $sliceCallable;

    /**
     * @param callable(Cursor|null $cursor, positive-int $limit): CursorSlice<T> $sliceCallable              Returns the slice for the cursor, see {@see CursorAdapterInterface::getSlice()} for the contract it must follow
     * @param bool                                                               $supportsBackwardNavigation Whether the callable supports cursors with the previous direction
     */
    public function __construct(
        callable $sliceCallable,
        private readonly bool $supportsBackwardNavigation = false,
    ) {
        $this->sliceCallable = $sliceCallable;
    }

    public function supportsBackwardNavigation(): bool
    {
        return $this->supportsBackwardNavigation;
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorSlice<T>
     *
     * @throws LogicException if the callable does not return a {@see CursorSlice}
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        $callable = $this->sliceCallable;

        $slice = $callable($cursor, $limit);

        if (!$slice instanceof CursorSlice) {
            throw new LogicException(\sprintf('The callable in "%s()" must return an instance of "%s", "%s" returned.', __METHOD__, CursorSlice::class, get_debug_type($slice)));
        }

        return $slice;
    }
}
