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
     * @param Selectable<TKey, T> $selectable
     */
    public function __construct(
        private readonly Selectable $selectable,
        private readonly Criteria $criteria,
    ) {}

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->selectable->matching($this->createCriteria(0, null))->count();
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
     * @phpstan-param int<0, max> $firstResult
     * @phpstan-param int<0, max>|null $maxResult
     */
    private function createCriteria(int $firstResult, ?int $maxResult): Criteria
    {
        $criteria = clone $this->criteria;
        $criteria->setFirstResult($firstResult);
        $criteria->setMaxResults($maxResult);

        return $criteria;
    }
}
