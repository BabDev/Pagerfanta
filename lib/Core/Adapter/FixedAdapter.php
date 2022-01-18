<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which returns a fixed data set.
 *
 * Best used when you need to do a custom paging solution and don't want to implement a full adapter for a one-off use case.
 *
 * @template T
 * @implements AdapterInterface<T>
 */
class FixedAdapter implements AdapterInterface
{
    /**
     * @phpstan-var int<0, max>
     */
    private int $nbResults;

    /**
     * @var iterable<array-key, T>
     */
    private iterable $results;

    /**
     * @param iterable<array-key, T> $results
     *
     * @phpstan-param int<0, max> $nbResults
     */
    public function __construct(int $nbResults, iterable $results)
    {
        $this->nbResults = $nbResults;
        $this->results = $results;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->results;
    }
}
