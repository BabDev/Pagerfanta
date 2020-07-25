<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

interface PagerfantaInterface extends \Countable, \IteratorAggregate
{
    public function getAdapter(): AdapterInterface;

    public function setAllowOutOfRangePages(bool $allowOutOfRangePages): PagerfantaInterface;

    public function getAllowOutOfRangePages(): bool;

    public function setNormalizeOutOfRangePages(bool $normalizeOutOfRangePages): PagerfantaInterface;

    public function getNormalizeOutOfRangePages(): bool;

    /**
     * @throws LessThan1MaxPerPageException if the page is less than 1
     */
    public function setMaxPerPage(int $maxPerPage): PagerfantaInterface;

    public function getMaxPerPage(): int;

    /**
     * @throws LessThan1CurrentPageException  if the current page is less than 1
     * @throws OutOfRangeCurrentPageException if It is not allowed out of range pages and they are not normalized
     */
    public function setCurrentPage(int $currentPage): PagerfantaInterface;

    public function getCurrentPage(): int;

    public function getCurrentPageResults(): iterable;

    public function getCurrentPageOffsetStart(): int;

    public function getCurrentPageOffsetEnd(): int;

    public function getNbResults(): int;

    public function getNbPages(): int;

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
