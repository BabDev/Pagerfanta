<?php

namespace Pagerfanta\Adapter;

use Doctrine\Common\Collections\Collection;

/**
 * DoctrineCollectionAdapter.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class DoctrineCollectionAdapter implements AdapterInterface
{
    private $collection;

    /**
     * Constructor.
     *
     * @param Collection $collection a Doctrine collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Returns the collection.
     *
     * @return Collection the collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    public function getNbResults()
    {
        return $this->collection->count();
    }

    public function getSlice($offset, $length)
    {
        return $this->collection->slice($offset, $length);
    }
}
