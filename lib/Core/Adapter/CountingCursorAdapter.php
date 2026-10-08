<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Exception\NotValidResultCountException;

/**
 * Adapter which adds the total number of results to another cursor adapter.
 *
 * Use this to make a cursor adapter which cannot count its results countable.
 *
 * @template T
 *
 * @implements CursorAdapterInterface<T>
 */
class CountingCursorAdapter implements CursorAdapterInterface, CountableAdapterInterface
{
    /**
     * @var (callable(): int<0, max>)|CountableAdapterInterface
     */
    private $counter;

    /**
     * @param CursorAdapterInterface<T>                           $adapter
     * @param (callable(): int<0, max>)|CountableAdapterInterface $counter A callable returning the number of results, or an adapter to count the results with
     */
    public function __construct(
        private readonly CursorAdapterInterface $adapter,
        callable|CountableAdapterInterface $counter,
    ) {
        $this->counter = $counter;
    }

    /**
     * @return int<0, max>
     *
     * @throws NotValidResultCountException if the number of results is less than zero
     */
    public function getNbResults(): int
    {
        if ($this->counter instanceof CountableAdapterInterface) {
            return $this->counter->getNbResults();
        }

        $counter = $this->counter;

        $count = $counter();

        if ($count < 0) {
            throw new NotValidResultCountException(\sprintf('The callable to calculate the number of results in "%s()" must return a number greater than or equal to zero.', __METHOD__));
        }

        return $count;
    }

    public function supportsBackwardNavigation(): bool
    {
        return $this->adapter->supportsBackwardNavigation();
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorSlice<T>
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        return $this->adapter->getSlice($cursor, $limit);
    }
}
