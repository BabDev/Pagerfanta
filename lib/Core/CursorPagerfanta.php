<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;

/**
 * An immutable pager using cursor based pagination.
 *
 * @template T
 *
 * @implements CursorPagerInterface<T>
 */
final class CursorPagerfanta implements CursorPagerInterface, \Countable, \JsonSerializable
{
    /**
     * @var CursorSlice<T>|null
     */
    private ?CursorSlice $slice = null;

    /**
     * @param CursorAdapterInterface<T> $adapter
     * @param positive-int              $maxPerPage
     * @param CursorPosition|null       $currentPosition The position of the current page, or null for the first page
     *
     * @throws LessThan1MaxPerPageException if the max per page is less than 1
     */
    public function __construct(
        private readonly CursorAdapterInterface $adapter,
        private readonly int $maxPerPage = 10,
        private readonly ?CursorPosition $currentPosition = null,
    ) {
        if ($maxPerPage < 1) {
            throw new LessThan1MaxPerPageException();
        }
    }

    /**
     * @return CursorAdapterInterface<T>
     */
    public function getAdapter(): CursorAdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Returns a new pager for the given position, keeping this pager's configuration.
     *
     * @param CursorPosition|null $position The position of the page, or null for the first page
     *
     * @return self<T>
     */
    public function withPosition(?CursorPosition $position): self
    {
        return new self($this->adapter, $this->maxPerPage, $position);
    }

    public function getCurrentPosition(): ?CursorPosition
    {
        return $this->currentPosition;
    }

    /**
     * @return positive-int
     */
    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    /**
     * @return list<T>
     */
    public function getCurrentPageResults(): array
    {
        return $this->getSlice()->items;
    }

    public function supportsBackwardNavigation(): bool
    {
        return $this->adapter->supportsBackwardNavigation();
    }

    public function haveToPaginate(): bool
    {
        return $this->hasPreviousPage() || $this->hasNextPage();
    }

    public function hasPreviousPage(): bool
    {
        return $this->supportsBackwardNavigation() && $this->getSlice()->previous instanceof Cursor;
    }

    public function hasNextPage(): bool
    {
        return $this->getSlice()->next instanceof Cursor;
    }

    /**
     * @throws LogicException if there is no previous page
     */
    public function getPreviousPosition(): CursorPosition
    {
        $cursor = $this->getSlice()->previous;

        if (!$this->supportsBackwardNavigation() || !$cursor instanceof Cursor) {
            throw new LogicException('There is no previous page.');
        }

        return new CursorPosition($cursor);
    }

    /**
     * @throws LogicException if there is no next page
     */
    public function getNextPosition(): CursorPosition
    {
        $cursor = $this->getSlice()->next;

        if (!$cursor instanceof Cursor) {
            throw new LogicException('There is no next page.');
        }

        return new CursorPosition($cursor);
    }

    /**
     * Returns the number of items on the current page.
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->getCurrentPageResults());
    }

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getCurrentPageResults());
    }

    /**
     * @return list<T>
     */
    public function jsonSerialize(): array
    {
        return $this->getCurrentPageResults();
    }

    /**
     * Generates an iterator to automatically iterate over all pages in a result set, starting from the current page.
     *
     * @return \Generator<int, T, mixed, void>
     */
    public function autoPagingIterator(): \Generator
    {
        $pager = $this;

        while (true) {
            foreach ($pager->getCurrentPageResults() as $item) {
                yield $item;
            }

            if (!$pager->hasNextPage()) {
                break;
            }

            $pager = $pager->withPosition($pager->getNextPosition());
        }
    }

    /**
     * @return CursorSlice<T>
     *
     * @throws LogicException if the adapter returns more items than the max per page
     */
    private function getSlice(): CursorSlice
    {
        if (!$this->slice instanceof CursorSlice) {
            $slice = $this->adapter->getSlice($this->currentPosition?->cursor, $this->maxPerPage);

            if (\count($slice->items) > $this->maxPerPage) {
                throw new LogicException(\sprintf('The "%s" adapter returned %d items, but the limit is %d. Adapters must trim the extra item used to detect another page before returning a slice.', get_debug_type($this->adapter), \count($slice->items), $this->maxPerPage));
            }

            $this->slice = $slice;
        }

        return $this->slice;
    }
}
