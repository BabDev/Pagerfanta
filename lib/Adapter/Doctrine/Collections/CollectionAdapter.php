<?php

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
    private $collection;

    /**
     * @param Collection<TKey, T> $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Retrieves the Collection.
     *
     * @return Collection<TKey, T>
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->collection->count();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        return $this->collection->slice($offset, $length);
    }
}
