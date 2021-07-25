<?php

namespace Pagerfanta\Adapter;

interface AdapterInterface
{
    /**
     * Returns the number of results for the list.
     *
     * @return int
     */
    public function getNbResults();

    /**
     * Returns an slice of the results representing the current page of items in the list.
     *
     * @param int $offset
     * @param int $length
     *
     * @return iterable<array-key, mixed>
     */
    public function getSlice($offset, $length);
}
