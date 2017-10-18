<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * Adapter that concatenates the results of other adapters.
 *
 * @author Surgie Finesse <finesserus@gmail.com>
 */
class ConcatenationAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface[] List of adapters
     */
    protected $adapters;

    /**
     * @var int[]|null Cache of the numbers of results of the adapters. The indexes correspond the indexes of the
     * `adapters` property.
     */
    protected $adaptersNbResultsCache;

    /**
     * @param AdapterInterface[] $adapters
     * @throws InvalidArgumentException
     */
    public function __construct(array $adapters)
    {
        foreach ($adapters as $index => $adapter) {
            if (!($adapter instanceof AdapterInterface)) {
                throw new InvalidArgumentException(sprintf(
                    'Argument $adapters[%s] expected to be a \Pagerfanta\Adapter\AdapterInterface instance, a %s given',
                    $index,
                    is_object($adapter) ? sprintf('%s instance', get_class($adapter)) : gettype($adapter)
                ));
            }
        }

        $this->adapters = $adapters;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        if (!isset($this->adaptersNbResultsCache)) {
            $this->refreshAdaptersNbResults();
        }

        return array_sum($this->adaptersNbResultsCache);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getSlice($offset, $length)
    {
        if (!isset($this->adaptersNbResultsCache)) {
            $this->refreshAdaptersNbResults();
        }

        $slice = array();
        $previousAdaptersNbResultsSum = 0;
        $requestFirstIndex = $offset;
        $requestLastIndex  = $offset + $length - 1;

        foreach ($this->adapters as $index => $adapter) {
            $adapterNbResults  = $this->adaptersNbResultsCache[$index];
            $adapterFirstIndex = $previousAdaptersNbResultsSum;
            $adapterLastIndex  = $adapterFirstIndex + $adapterNbResults - 1;

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
    protected function refreshAdaptersNbResults()
    {
        if (!isset($this->adaptersNbResultsCache)) {
            $this->adaptersNbResultsCache = array();
        }

        foreach ($this->adapters as $index => $adapter) {
            $this->adaptersNbResultsCache[$index] = $adapter->getNbResults();
        }
    }
}
