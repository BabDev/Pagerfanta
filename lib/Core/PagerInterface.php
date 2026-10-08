<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\Position;

/**
 * The root pager API, independent of the pagination strategy.
 *
 * This interface intentionally does not extend {@see \Countable} as the meaning of `count()` differs between the
 * existing offset pager (the total number of results) and the cursor pagers (the number of items on the current page).
 * In 5.0, this interface will extend {@see \Countable} with `count()` returning the number of items on the current page.
 *
 * @template-covariant T
 * @template-covariant TPosition of Position
 *
 * @extends \IteratorAggregate<T>
 */
interface PagerInterface extends \IteratorAggregate /* , \Countable */
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
