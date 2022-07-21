<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * @template-covariant T
 */
interface AdapterInterface
{
    /**
     * Returns the number of results for the list.
     *
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int;

    /**
     * Returns a slice of the results representing the current page of items in the list.
     *
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable;
}
