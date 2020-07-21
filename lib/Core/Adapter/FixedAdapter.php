<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which returns a fixed data set.
 *
 * Best used when you need to do a custom paging solution and don't want to implement a full adapter for a one-off use case.
 */
class FixedAdapter implements AdapterInterface
{
    private int $nbResults;
    private iterable $results;

    public function __construct(int $nbResults, iterable $results)
    {
        $this->nbResults = $nbResults;
        $this->results = $results;
    }

    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->results;
    }
}
