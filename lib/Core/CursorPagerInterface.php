<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Position\CursorPosition;

/**
 * A pager using cursor (keyset) based pagination.
 *
 * Cursor pagers always know whether a next page exists, and whether a previous page exists when the underlying adapter
 * supports backward navigation. The total number of results is only available when the pager also implements
 * {@see CountablePagerInterface}.
 *
 * @template-covariant T
 *
 * @extends PagerInterface<T, CursorPosition>
 */
interface CursorPagerInterface extends PagerInterface
{
    /**
     * @return CursorPosition|null The position of the current page, or null when on the first page
     */
    public function getCurrentPosition(): ?CursorPosition;

    public function supportsBackwardNavigation(): bool;
}
