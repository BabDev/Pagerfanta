<?php declare(strict_types=1);

namespace Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\LessThan1MaxPagesException;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Exception\OutOfBoundsException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

class Pagerfanta implements PagerfantaInterface, \JsonSerializable
{
    private AdapterInterface $adapter;
    private bool $allowOutOfRangePages = false;
    private bool $normalizeOutOfRangePages = false;
    private int $maxPerPage = 10;
    private int $currentPage = 1;
    private ?int $nbResults = null;
    private ?int $maxNbPages = null;
    private ?iterable $currentPageResults = null;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    public function setAllowOutOfRangePages(bool $allowOutOfRangePages): PagerfantaInterface
    {
        $this->allowOutOfRangePages = $allowOutOfRangePages;

        return $this;
    }

    public function getAllowOutOfRangePages(): bool
    {
        return $this->allowOutOfRangePages;
    }

    public function setNormalizeOutOfRangePages(bool $normalizeOutOfRangePages): PagerfantaInterface
    {
        $this->normalizeOutOfRangePages = $normalizeOutOfRangePages;

        return $this;
    }

    public function getNormalizeOutOfRangePages(): bool
    {
        return $this->normalizeOutOfRangePages;
    }

    /**
     * @throws LessThan1MaxPerPageException if the page is less than 1
     */
    public function setMaxPerPage(int $maxPerPage): PagerfantaInterface
    {
        $this->filterMaxPerPage($maxPerPage);

        $this->maxPerPage = $maxPerPage;
        $this->resetForMaxPerPageChange();

        return $this;
    }

    private function filterMaxPerPage(int $maxPerPage): void
    {
        $this->checkMaxPerPage($maxPerPage);
    }

    /**
     * @throws LessThan1MaxPerPageException if the page is less than 1
     */
    private function checkMaxPerPage(int $maxPerPage): void
    {
        if ($maxPerPage < 1) {
            throw new LessThan1MaxPerPageException();
        }
    }

    private function resetForMaxPerPageChange(): void
    {
        $this->currentPageResults = null;
        $this->nbResults = null;
    }

    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    /**
     * @throws LessThan1CurrentPageException  if the current page is less than 1
     * @throws OutOfRangeCurrentPageException if It is not allowed out of range pages and they are not normalized
     */
    public function setCurrentPage(int $currentPage): PagerfantaInterface
    {
        $this->currentPage = $this->filterCurrentPage($currentPage);
        $this->resetForCurrentPageChange();

        return $this;
    }

    private function filterCurrentPage(int $currentPage): int
    {
        $this->checkCurrentPage($currentPage);

        return $this->filterOutOfRangeCurrentPage($currentPage);
    }

    /**
     * @throws LessThan1CurrentPageException if the current page is less than 1
     */
    private function checkCurrentPage(int $currentPage): void
    {
        if ($currentPage < 1) {
            throw new LessThan1CurrentPageException();
        }
    }

    private function filterOutOfRangeCurrentPage(int $currentPage): int
    {
        if ($this->notAllowedCurrentPageOutOfRange($currentPage)) {
            return $this->normalizeOutOfRangeCurrentPage($currentPage);
        }

        return $currentPage;
    }

    private function notAllowedCurrentPageOutOfRange(int $currentPage): bool
    {
        return !$this->getAllowOutOfRangePages() && $this->currentPageOutOfRange($currentPage);
    }

    private function currentPageOutOfRange(int $currentPage): bool
    {
        return $currentPage > 1 && $currentPage > $this->getNbPages();
    }

    /**
     * @throws OutOfRangeCurrentPageException if the page should not be normalized
     */
    private function normalizeOutOfRangeCurrentPage(int $currentPage): int
    {
        if ($this->getNormalizeOutOfRangePages()) {
            return $this->getNbPages();
        }

        throw new OutOfRangeCurrentPageException(sprintf('Page "%d" does not exist. The currentPage must be inferior to "%d"', $currentPage, $this->getNbPages()));
    }

    private function resetForCurrentPageChange(): void
    {
        $this->currentPageResults = null;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getCurrentPageResults(): iterable
    {
        if ($this->notCachedCurrentPageResults()) {
            $this->currentPageResults = $this->getCurrentPageResultsFromAdapter();
        }

        return $this->currentPageResults;
    }

    private function notCachedCurrentPageResults(): bool
    {
        return null === $this->currentPageResults;
    }

    private function getCurrentPageResultsFromAdapter(): iterable
    {
        $offset = $this->calculateOffsetForCurrentPageResults();
        $length = $this->getMaxPerPage();

        return $this->adapter->getSlice($offset, $length);
    }

    private function calculateOffsetForCurrentPageResults(): int
    {
        return ($this->getCurrentPage() - 1) * $this->getMaxPerPage();
    }

    public function getCurrentPageOffsetStart(): int
    {
        return $this->getNbResults() ? $this->calculateOffsetForCurrentPageResults() + 1 : 0;
    }

    public function getCurrentPageOffsetEnd(): int
    {
        return $this->hasNextPage() ? $this->getCurrentPage() * $this->getMaxPerPage() : $this->getNbResults();
    }

    public function getNbResults(): int
    {
        if ($this->notCachedNbResults()) {
            $this->nbResults = $this->getAdapter()->getNbResults();
        }

        return $this->nbResults;
    }

    private function notCachedNbResults(): bool
    {
        return null === $this->nbResults;
    }

    public function getNbPages(): int
    {
        $nbPages = $this->calculateNbPages();

        if (0 === $nbPages) {
            return $this->minimumNbPages();
        }

        if (null !== $this->maxNbPages && $this->maxNbPages < $nbPages) {
            return $this->maxNbPages;
        }

        return $nbPages;
    }

    private function calculateNbPages(): int
    {
        return (int) ceil($this->getNbResults() / $this->getMaxPerPage());
    }

    private function minimumNbPages(): int
    {
        return 1;
    }

    /**
     * @throws LessThan1MaxPagesException if the max number of pages is less than 1
     */
    public function setMaxNbPages(int $maxNbPages): PagerfantaInterface
    {
        if ($maxNbPages < 1) {
            throw new LessThan1MaxPagesException();
        }

        $this->maxNbPages = $maxNbPages;

        return $this;
    }

    public function resetMaxNbPages(): PagerfantaInterface
    {
        $this->maxNbPages = null;

        return $this;
    }

    public function haveToPaginate(): bool
    {
        return $this->getNbResults() > $this->maxPerPage;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * @throws LogicException if there is no previous page
     */
    public function getPreviousPage(): int
    {
        if (!$this->hasPreviousPage()) {
            throw new LogicException('There is no previous page.');
        }

        return $this->currentPage - 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getNbPages();
    }

    /**
     * @throws LogicException if there is no next page
     */
    public function getNextPage(): int
    {
        if (!$this->hasNextPage()) {
            throw new LogicException('There is no next page.');
        }

        return $this->currentPage + 1;
    }

    public function count(): int
    {
        return $this->getNbResults();
    }

    /**
     * @return \Traversable<mixed>
     */
    public function getIterator(): \Traversable
    {
        $results = $this->getCurrentPageResults();

        if ($results instanceof \Iterator) {
            return $results;
        }

        if ($results instanceof \IteratorAggregate) {
            return $results->getIterator();
        }

        return new \ArrayIterator($results);
    }

    public function jsonSerialize(): array
    {
        $results = $this->getCurrentPageResults();

        if ($results instanceof \Traversable) {
            return iterator_to_array($results);
        }

        return $results;
    }

    /**
     * Get page number of the item at specified position (1-based index).
     *
     * @throws OutOfBoundsException if the item is outside the result set
     */
    public function getPageNumberForItemAtPosition(int $position): int
    {
        if ($this->getNbResults() < $position) {
            throw new OutOfBoundsException(sprintf('Item requested at position %d, but there are only %d items.', $position, $this->getNbResults()));
        }

        return (int) ceil($position / $this->getMaxPerPage());
    }
}
