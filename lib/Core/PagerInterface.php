<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\Position;

/**
 * The root pager API, independent of the pagination strategy.
 *
 * Counting a pager returns the number of items on the current page. The total number of results is only available from
 * pagers implementing {@see CountablePagerInterface}.
 *
 * @template-covariant T
 * @template-covariant TPosition of Position
 *
 * @extends \IteratorAggregate<T>
 */
interface PagerInterface extends \Countable, \IteratorAggregate
{
    /**
     * @return iterable<array-key, T>
     */
    public function getCurrentPageResults(): iterable;

    /**
     * @return positive-int
     */
    public function getMaxPerPage(): int;

    public function haveToPaginate(): bool;

    public function hasPreviousPage(): bool;

    public function hasNextPage(): bool;

    /**
     * @return TPosition
     *
     * @throws LogicException if there is no previous page
     */
    public function getPreviousPosition(): Position;

    /**
     * @return TPosition
     *
     * @throws LogicException if there is no next page
     */
    public function getNextPosition(): Position;
}
