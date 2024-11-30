<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\LessThan1MaxPagesException;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

/**
 * @template-covariant T
 *
 * @extends \IteratorAggregate<T>
 *
 * @method \Generator<int, T, mixed, void> autoPagingIterator()
 */
interface PagerfantaInterface extends \Countable, \IteratorAggregate
{
    /**
     * @return AdapterInterface<T>
     */
    public function getAdapter(): AdapterInterface;

    /**
     * @return $this
     *
     * @phpstan-self-out self<T>
     */
    public function setAllowOutOfRangePages(bool $allowOutOfRangePages): self;

    public function getAllowOutOfRangePages(): bool;

    /**
     * @return $this
     *
     * @phpstan-self-out self<T>
     */
    public function setNormalizeOutOfRangePages(bool $normalizeOutOfRangePages): self;

    public function getNormalizeOutOfRangePages(): bool;

    /**
     * @return $this
     *
     * @phpstan-self-out self<T>
     *
     * @throws LessThan1MaxPerPageException if the page is less than 1
     */
    public function setMaxPerPage(int $maxPerPage): self;

    /**
     * @return positive-int
     */
    public function getMaxPerPage(): int;

    /**
     * @return $this
     *
     * @phpstan-self-out self<T>
     *
     * @throws LessThan1CurrentPageException  if the current page is less than 1
     * @throws OutOfRangeCurrentPageException if It is not allowed out of range pages and they are not normalized
     */
    public function setCurrentPage(int $currentPage): self;

    /**
     * @return positive-int
     */
    public function getCurrentPage(): int;

    /**
     * @return iterable<array-key, T>
     */
    public function getCurrentPageResults(): iterable;

    /**
     * @return int<0, max>
     */
    public function getCurrentPageOffsetStart(): int;

    /**
     * @return int<0, max>
     */
    public function getCurrentPageOffsetEnd(): int;

    /**
     * @return int<0, max>
     */
    public function getNbResults(): int;

    /**
     * @return positive-int
     */
    public function getNbPages(): int;

    /**
     * @return $this
     *
     * @phpstan-self-out self<T>
     *
     * @throws LessThan1MaxPagesException if the max number of pages is less than 1
     */
    public function setMaxNbPages(int $maxNbPages): self;

    /**
     * @return $this
     *
     * @phpstan-self-out self<T>
     */
    public function resetMaxNbPages(): self;

    public function haveToPaginate(): bool;

    public function hasPreviousPage(): bool;

    /**
     * @return positive-int
     *
     * @throws LogicException if there is no previous page
     */
    public function getPreviousPage(): int;

    public function hasNextPage(): bool;

    /**
     * @return positive-int
     *
     * @throws LogicException if there is no next page
     */
    public function getNextPage(): int;

    /**
     * Get page number of the item at specified position (1-based index).
     *
     * @param positive-int $position
     *
     * @return positive-int
     */
    public function getPageNumberForItemAtPosition(int $position): int;
}
