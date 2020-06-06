<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

interface AdapterInterface
{
    /**
     * Returns the number of results for the list.
     */
    public function getNbResults(): int;

    /**
     * Returns an slice of the results representing the current page of items in the list.
     */
    public function getSlice(int $offset, int $length): iterable;
}
