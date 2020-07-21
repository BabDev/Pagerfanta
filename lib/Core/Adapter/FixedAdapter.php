<?php

namespace Pagerfanta\Adapter;

/**
 * Adapter which returns a fixed data set.
 *
 * Best used when you need to do a custom paging solution and don't want to implement a full adapter for a one-off use case.
 */
class FixedAdapter implements AdapterInterface
{
    /**
     * @var int
     */
    private $nbResults;

    /**
     * @var iterable
     */
    private $results;

    /**
     * @param int      $nbResults
     * @param iterable $results
     */
    public function __construct($nbResults, $results)
    {
        $this->nbResults = $nbResults;
        $this->results = $results;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        return $this->results;
    }
}
