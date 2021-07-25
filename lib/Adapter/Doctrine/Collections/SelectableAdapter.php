<?php

namespace Pagerfanta\Doctrine\Collections;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Selectable instance.
 *
 * @template TKey of array-key
 * @template T
 */
class SelectableAdapter implements AdapterInterface
{
    /**
     * @var Selectable<TKey, T>
     */
    private $selectable;

    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @param Selectable<TKey, T> $selectable
     */
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
