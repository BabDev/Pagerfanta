<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections;

use Doctrine\Common\Collections\Collection;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine Collection.
 */
class CollectionAdapter implements AdapterInterface
{
    private Collection $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function getCollection(): Collection
    {
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
