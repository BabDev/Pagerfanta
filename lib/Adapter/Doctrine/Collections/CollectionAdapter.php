<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections;

use Doctrine\Common\Collections\Collection;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine Collection.
 *
 * @template TKey of array-key
 * @template T
 */
class CollectionAdapter implements AdapterInterface
{
    /**
     * @var Collection<TKey, T>
     */
    private Collection $collection;

    /**
     * @param Collection<TKey, T> $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return Collection<TKey, T>
     *
     * @deprecated to be removed in 4.0
     */
    public function getCollection(): Collection
    {
        trigger_deprecation('pagerfanta/pagerfanta', '3.2', 'Retrieving the %s from "%s" is deprecated and will be removed in 4.0.', Collection::class, static::class);

        return $this->collection;
    }

    public function getNbResults(): int
    {
        return $this->collection->count();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->collection->slice($offset, $length);
    }
}
