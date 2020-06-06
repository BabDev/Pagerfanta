<?php

namespace Pagerfanta\Adapter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

/**
 * Adapter which calculates pagination from a Selectable instance.
 */
class DoctrineSelectableAdapter implements AdapterInterface
{
    private Selectable $selectable;
    private Criteria $criteria;

    public function __construct(Selectable $selectable, Criteria $criteria)
    {
        $this->selectable = $selectable;
        $this->criteria = $criteria;
    }

    public function getNbResults(): int
    {
        return $this->selectable->matching($this->createCriteria(null, null))->count();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->selectable->matching($this->createCriteria($offset, $length));
    }

    private function createCriteria(?int $firstResult, ?int $maxResult): Criteria
    {
        $criteria = clone $this->criteria;
        $criteria->setFirstResult($firstResult);
        $criteria->setMaxResults($maxResult);

        return $criteria;
    }
}
