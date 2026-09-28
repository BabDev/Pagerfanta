<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Cursor\Cursor;

/**
 * Adapter which transforms the result of another cursor adapter.
 *
 * The cursors are created by the decorated adapter from the untransformed items.
 *
 * @template T
 *
 * @template-covariant Transformed
 *
 * @implements CursorAdapterInterface<Transformed>
 */
class TransformingCursorAdapter implements CursorAdapterInterface
{
    /**
     * @var callable(T, int<0, max>): Transformed
     */
    private $transformer;

    /**
     * @param CursorAdapterInterface<T>             $adapter
     * @param callable(T, int<0, max>): Transformed $transformer
     */
    public function __construct(
        private readonly CursorAdapterInterface $adapter,
        callable $transformer,
    ) {
        $this->transformer = $transformer;
    }

    public function supportsBackwardNavigation(): bool
    {
        return $this->adapter->supportsBackwardNavigation();
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorSlice<Transformed>
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        $slice = $this->adapter->getSlice($cursor, $limit);

        return new CursorSlice(
            array_map($this->transformer, $slice->items, array_keys($slice->items)),
            $slice->previous,
            $slice->next,
        );
    }
}
