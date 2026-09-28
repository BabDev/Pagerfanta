<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Exception\OutOfBoundsException;
use Pagerfanta\Position\PagePosition;

/**
 * A pager using offset (page number) based pagination.
 *
 * @template-covariant T
 *
 * @extends CountablePagerInterface<T, PagePosition>
 */
interface OffsetPagerInterface extends CountablePagerInterface
{
    /**
     * @return positive-int
     */
    public function getCurrentPage(): int;

    /**
     * @return positive-int
     */
    public function getNbPages(): int;

    /**
     * Get page number of the item at specified position (1-based index).
     *
     * @param positive-int $position
     *
     * @return positive-int
     *
     * @throws OutOfBoundsException if the item is outside the result set
     */
    public function getPageNumberForItemAtPosition(int $position): int;
}
