<?php

namespace Pagerfanta\Adapter;

use Doctrine\Common\Collections\Collection;

/**
 * Adapter which calculates pagination from a Doctrine Collection.
 */
class DoctrineCollectionAdapter implements AdapterInterface
{
    /**
     * @var Collection
     */
    private $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Retrieves the Collection.
     *
     * @return Collection
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
