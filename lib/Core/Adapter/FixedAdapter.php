<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Exception\NotValidResultCountException;

/**
 * Adapter which returns a fixed data set.
 *
 * Best used when you need to do a custom paging solution and don't want to implement a full adapter for a one-off use case.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class FixedAdapter implements AdapterInterface
{
    /**
     * @phpstan-var int<0, max>
     */
    private readonly int $nbResults;

    /**
     * @param iterable<array-key, T> $results
     *
     * @throws NotValidResultCountException if the number of results is less than zero
     */
    public function __construct(
        int $nbResults,
        private readonly iterable $results,
    ) {
        if ($nbResults < 0) {
            throw new NotValidResultCountException(sprintf('The number of results for the "%s" constructor must be at least zero.', static::class));
        }

        $this->nbResults = $nbResults;
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
