<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections;

use Doctrine\Common\Collections\ReadableCollection;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine Collection.
 *
 * @template TKey of array-key
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class CollectionAdapter implements AdapterInterface
{
    /**
     * @param ReadableCollection<TKey, T> $collection
     */
    public function __construct(
        private readonly ReadableCollection $collection,
    ) {}

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->collection->count();
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<TKey, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->collection->slice($offset, $length);
    }
}
