<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Selectable instance.
 *
 * @template TKey of array-key
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class SelectableAdapter implements AdapterInterface
{
    /**
     * @var Selectable<TKey, T>
     */
    private Selectable $selectable;
    private Criteria $criteria;

    /**
     * @param Selectable<TKey, T> $selectable
     */
    public function __construct(Selectable $selectable, Criteria $criteria)
    {
        $this->selectable = $selectable;
        $this->criteria = $criteria;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->selectable->matching($this->createCriteria(null, null))->count();
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->selectable->matching($this->createCriteria($offset, $length));
    }

    /**
     * @phpstan-param int<0, max>|null $firstResult
     * @phpstan-param int<0, max>|null $maxResult
     */
    private function createCriteria(?int $firstResult, ?int $maxResult): Criteria
    {
        $criteria = clone $this->criteria;
        $criteria->setFirstResult($firstResult);
        $criteria->setMaxResults($maxResult);

        return $criteria;
    }
}
