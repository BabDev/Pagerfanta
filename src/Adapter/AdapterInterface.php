<?php

namespace Pagerfanta\Adapter;

/**
 * AdapterInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
interface AdapterInterface
{
    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults();

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return iterable the slice
     */
    public function getSlice($offset, $length);
}
