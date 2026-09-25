<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Adapter\CountableAdapterInterface;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;

/**
 * An immutable pager using cursor based pagination, which also knows the total number of results.
 *
 * @template T
 *
 * @implements CursorPagerInterface<T>
 * @implements CountablePagerInterface<T, CursorPosition>
 */
final class CountableCursorPagerfanta implements CursorPagerInterface, CountablePagerInterface, \Countable, \JsonSerializable
{
    /**
     * @var CursorPagerfanta<T>
     */
    private readonly CursorPagerfanta $pager;

    /**
     * @var int<0, max>|null
     */
    private ?int $nbResults = null;

    /**
     * @param CursorAdapterInterface<T>&CountableAdapterInterface $adapter
     * @param positive-int                                        $maxPerPage
     * @param CursorPosition|null                                 $currentPosition The position of the current page, or null for the first page
     *
     * @throws LessThan1MaxPerPageException if the max per page is less than 1
     */
    public function __construct(
        private readonly CursorAdapterInterface&CountableAdapterInterface $adapter,
        int $maxPerPage = 10,
        ?CursorPosition $currentPosition = null,
    ) {
        $this->pager = new CursorPagerfanta($adapter, $maxPerPage, $currentPosition);
    }

    /**
     * @return CursorAdapterInterface<T>&CountableAdapterInterface
     */
    public function getAdapter(): CursorAdapterInterface&CountableAdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Returns a new pager for the given position, keeping this pager's configuration.
     *
     * The total number of results is not carried over to the new pager.
     *
     * @param CursorPosition|null $position The position of the page, or null for the first page
     *
     * @return self<T>
     */
    public function withPosition(?CursorPosition $position): self
    {
        return new self($this->adapter, $this->pager->getMaxPerPage(), $position);
    }

    /**
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->nbResults ??= $this->adapter->getNbResults();
    }

    public function getCurrentPosition(): ?CursorPosition
    {
        return $this->pager->getCurrentPosition();
    }

    /**
     * @return positive-int
     */
    public function getMaxPerPage(): int
    {
        return $this->pager->getMaxPerPage();
    }

    /**
     * @return list<T>
     */
    public function getCurrentPageResults(): array
    {
        return $this->pager->getCurrentPageResults();
    }

    public function supportsBackwardNavigation(): bool
    {
        return $this->pager->supportsBackwardNavigation();
    }

    public function haveToPaginate(): bool
    {
        return $this->pager->haveToPaginate();
    }

    public function hasPreviousPage(): bool
    {
        return $this->pager->hasPreviousPage();
    }

    public function hasNextPage(): bool
    {
        return $this->pager->hasNextPage();
    }

    /**
     * @throws LogicException if there is no previous page
     */
    public function getPreviousPosition(): CursorPosition
    {
        return $this->pager->getPreviousPosition();
    }

    /**
     * @throws LogicException if there is no next page
     */
    public function getNextPosition(): CursorPosition
    {
        return $this->pager->getNextPosition();
    }

    /**
     * Returns the number of items on the current page, use {@see getNbResults()} for the total number of results.
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        return $this->pager->count();
    }

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return $this->pager->getIterator();
    }

    /**
     * @return list<T>
     */
    public function jsonSerialize(): array
    {
        return $this->pager->jsonSerialize();
    }

    /**
     * Generates an iterator to automatically iterate over all pages in a result set, starting from the current page.
     *
     * @return \Generator<int, T, mixed, void>
     */
    public function autoPagingIterator(): \Generator
    {
        return $this->pager->autoPagingIterator();
    }
}
