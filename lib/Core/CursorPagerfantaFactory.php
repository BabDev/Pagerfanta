<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Adapter\CountableAdapterInterface;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Position\CursorPosition;

/**
 * Creates the cursor pager matching the capabilities of an adapter.
 *
 * Adapters which implement {@see CountableAdapterInterface} get a {@see CountableCursorPagerfanta}, which exposes the total
 * number of results. All other adapters get a {@see CursorPagerfanta}. Use `instanceof CountablePagerInterface` to check
 * whether the total is available.
 */
final class CursorPagerfantaFactory
{
    /**
     * @template T
     *
     * @param CursorAdapterInterface<T> $adapter
     * @param positive-int              $maxPerPage
     * @param CursorPosition|null       $currentPosition The position of the current page, or null for the first page
     *
     * @return CursorPagerfanta<T>|CountableCursorPagerfanta<T>
     *
     * @throws LessThan1MaxPerPageException if the max per page is less than 1
     */
    public static function create(CursorAdapterInterface $adapter, int $maxPerPage = 10, ?CursorPosition $currentPosition = null): CursorPagerfanta|CountableCursorPagerfanta
    {
        if ($adapter instanceof CountableAdapterInterface) {
            return new CountableCursorPagerfanta($adapter, $maxPerPage, $currentPosition);
        }

        return new CursorPagerfanta($adapter, $maxPerPage, $currentPosition);
    }
}
