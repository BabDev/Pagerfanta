<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\LessThan1MaxPagesException;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

/**
 * @template T
 * @extends \IteratorAggregate<T>
 */
interface PagerfantaInterface extends \Countable, \IteratorAggregate
{
    /**
     * @deprecated to be removed in 4.0
     */
    public function getAdapter(): AdapterInterface;

    /**
     * @return $this<T>
     */
    public function setAllowOutOfRangePages(bool $allowOutOfRangePages): self;

    public function getAllowOutOfRangePages(): bool;

    /**
     * @return $this<T>
     */
    public function setNormalizeOutOfRangePages(bool $normalizeOutOfRangePages): self;

    public function getNormalizeOutOfRangePages(): bool;

    /**
     * @return $this<T>
     *
     * @throws LessThan1MaxPerPageException if the page is less than 1
     */
    public function setMaxPerPage(int $maxPerPage): self;

    public function getMaxPerPage(): int;

    /**
     * @return $this<T>
     *
     * @throws LessThan1CurrentPageException  if the current page is less than 1
     * @throws OutOfRangeCurrentPageException if It is not allowed out of range pages and they are not normalized
     */
    public function setCurrentPage(int $currentPage): self;

    public function getCurrentPage(): int;

    /**
     * @phpstan-return iterable<array-key, T>
     */
    public function getCurrentPageResults(): iterable;

    public function getCurrentPageOffsetStart(): int;

    public function getCurrentPageOffsetEnd(): int;

    public function getNbResults(): int;

    public function getNbPages(): int;

    /**
     * @return $this<T>
     *
     * @throws LessThan1MaxPagesException if the max number of pages is less than 1
     */
    public function setMaxNbPages(int $maxNbPages): self;

    /**
     * @return $this<T>
     */
    public function resetMaxNbPages(): self;

    public function haveToPaginate(): bool;

    public function hasPreviousPage(): bool;

    /**
     * @throws LogicException if there is no previous page
     */
    public function getPreviousPage(): int;

    public function hasNextPage(): bool;

    /**
     * @throws LogicException if there is no next page
     */
    public function getNextPage(): int;

    public function getPageNumberForItemAtPosition(int $position): int;
}
