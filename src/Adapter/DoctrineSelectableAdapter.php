<?php

namespace Pagerfanta\Adapter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

/**
 * Adapter which calculates pagination from a Selectable instance.
 */
class DoctrineSelectableAdapter implements AdapterInterface
{
    /**
     * @var Selectable
     */
    private $selectable;

    /**
     * @var Criteria
     */
    private $criteria;

    public function __construct(Selectable $selectable, Criteria $criteria)
    {
        $this->selectable = $selectable;
        $this->criteria = $criteria;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->selectable->matching($this->createCriteria(null, null))->count();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        return $this->selectable->matching($this->createCriteria($offset, $length));
    }

    /**
     * @param int|null $firstResult
     * @param int|null $maxResult
     */
    private function createCriteria($firstResult, $maxResult): Criteria
    {
        $criteria = clone $this->criteria;
        $criteria->setFirstResult($firstResult);
        $criteria->setMaxResults($maxResult);

        return $criteria;
    }
}
