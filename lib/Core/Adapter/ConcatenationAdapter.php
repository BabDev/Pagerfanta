<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * Adapter that concatenates the results of other adapters.
 *
 * @template T
 * @implements AdapterInterface<T>
 */
class ConcatenationAdapter implements AdapterInterface
{
    /**
     * @var array<array-key, AdapterInterface<T>>
     */
    protected array $adapters;

    /**
     * Cache of the numbers of results of the adapters. The indexes correspond the indexes of the $adapters property.
     *
     * @var array<array-key, int>|null
     */
    protected $adaptersNbResultsCache = null;

    /**
     * @param array<array-key, AdapterInterface<T>> $adapters
     *
     * @throws InvalidArgumentException if an adapter is not a `Pagerfanta\Adapter\AdapterInterface` instance
     */
    public function __construct(array $adapters)
    {
        foreach ($adapters as $adapter) {
            if (!($adapter instanceof AdapterInterface)) {
                throw new InvalidArgumentException(sprintf('The $adapters argument of the %s constructor expects all items to be an instance of %s.', self::class, AdapterInterface::class));
            }
        }

        $this->adapters = $adapters;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        if (null === $this->adaptersNbResultsCache) {
            $this->refreshAdaptersNbResults();
        }

        return array_sum($this->adaptersNbResultsCache);
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        if (null === $this->adaptersNbResultsCache) {
            $this->refreshAdaptersNbResults();
        }

        $slice = [];
        $previousAdaptersNbResultsSum = 0;
        $requestFirstIndex = $offset;
        $requestLastIndex = $offset + $length - 1;

        foreach ($this->adapters as $index => $adapter) {
            $adapterNbResults = $this->adaptersNbResultsCache[$index];
            $adapterFirstIndex = $previousAdaptersNbResultsSum;
            $adapterLastIndex = $adapterFirstIndex + $adapterNbResults - 1;

            $previousAdaptersNbResultsSum += $adapterNbResults;

            // The adapter is fully below the requested slice range — skip it
            if ($adapterLastIndex < $requestFirstIndex) {
                continue;
            }

            // The adapter is fully above the requested slice range — finish the gathering
            if ($adapterFirstIndex > $requestLastIndex) {
                break;
            }

            // Else the adapter range definitely intersects with the requested range
            $fetchOffset = $requestFirstIndex - $adapterFirstIndex;
            $fetchLength = $length;

            // The requested range start is below the adapter range start
            if ($fetchOffset < 0) {
                $fetchLength += $fetchOffset;
                $fetchOffset = 0;
            }

            // The requested range end is above the adapter range end
            if ($fetchOffset + $fetchLength > $adapterNbResults) {
                $fetchLength = $adapterNbResults - $fetchOffset;
            }

            // Getting the subslice from the adapter and adding it to the result slice
            $fetchSlice = $adapter->getSlice($fetchOffset, $fetchLength);

            foreach ($fetchSlice as $item) {
                $slice[] = $item;
            }
        }

        return $slice;
    }

    /**
     * Refreshes the cache of the numbers of results of the adapters.
     */
    protected function refreshAdaptersNbResults(): void
    {
        if (null === $this->adaptersNbResultsCache) {
            $this->adaptersNbResultsCache = [];
        }

        foreach ($this->adapters as $index => $adapter) {
            $this->adaptersNbResultsCache[$index] = $adapter->getNbResults();
        }
    }
}
