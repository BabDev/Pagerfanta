<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * An adapter supporting offset based pagination.
 *
 * @template-covariant T
 */
interface OffsetAdapterInterface
{
    /**
     * Returns a slice of the results representing the current page of items in the list.
     *
     * @param int<0, max> $offset
     * @param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable;
}
