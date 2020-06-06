<?php

namespace Pagerfanta;

use OutOfBoundsException;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Exception\NotBooleanException;
use Pagerfanta\Exception\NotIntegerCurrentPageException;
use Pagerfanta\Exception\NotIntegerException;
use Pagerfanta\Exception\NotIntegerMaxPerPageException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

/**
 * Represents a paginator.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Pagerfanta implements \Countable, \IteratorAggregate, \JsonSerializable, PagerfantaInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var bool
     */
    private $allowOutOfRangePages = false;

    /**
     * @var bool
     */
    private $normalizeOutOfRangePages = false;

    /**
     * @var int
     */
    private $maxPerPage = 10;

    /**
     * @var int
     */
    private $currentPage = 1;

    private $nbResults;
    private $currentPageResults;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns the adapter.
     *
     * @return AdapterInterface the adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Sets whether or not allow out of range pages.
     *
     * @param bool $value
     *
     * @return self
     */
    public function setAllowOutOfRangePages($value)
    {
        $this->allowOutOfRangePages = $this->filterBoolean($value);

        return $this;
    }

    /**
     * Returns whether or not allow out of range pages.
     *
     * @return bool
     */
    public function getAllowOutOfRangePages()
    {
        return $this->allowOutOfRangePages;
    }

    /**
     * Sets whether or not normalize out of range pages.
     *
     * @param bool $value
     *
     * @return self
     */
    public function setNormalizeOutOfRangePages($value)
    {
        $this->normalizeOutOfRangePages = $this->filterBoolean($value);

        return $this;
    }

    /**
     * Returns whether or not normalize out of range pages.
     *
     * @return bool
     */
    public function getNormalizeOutOfRangePages()
    {
        return $this->normalizeOutOfRangePages;
    }

    private function filterBoolean($value): bool
    {
        if (!\is_bool($value)) {
            throw new NotBooleanException();
        }

        return $value;
    }

    /**
     * Sets the max per page.
     *
     * Tries to convert from string and float.
     *
     * @param int $maxPerPage
     *
     * @return self
     *
     * @throws NotIntegerMaxPerPageException if the max per page is not an integer even converting
     * @throws LessThan1MaxPerPageException  if the max per page is less than 1
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $this->filterMaxPerPage($maxPerPage);
        $this->resetForMaxPerPageChange();

        return $this;
    }

    private function filterMaxPerPage($maxPerPage): int
    {
        $maxPerPage = $this->toInteger($maxPerPage);
        $this->checkMaxPerPage($maxPerPage);

        return $maxPerPage;
    }

    private function checkMaxPerPage($maxPerPage): void
    {
        if (!\is_int($maxPerPage)) {
            throw new NotIntegerMaxPerPageException();
        }

        if ($maxPerPage < 1) {
            throw new LessThan1MaxPerPageException();
        }
    }

    private function resetForMaxPerPageChange(): void
    {
        $this->currentPageResults = null;
        $this->nbResults = null;
    }

    /**
     * Returns the max per page.
     *
     * @return int
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * Sets the current page.
     *
     * Tries to convert from string and float.
     *
     * @param int $currentPage
     *
     * @return self
     *
     * @throws NotIntegerCurrentPageException if the current page is not an integer even converting
     * @throws LessThan1CurrentPageException  if the current page is less than 1
     * @throws OutOfRangeCurrentPageException if It is not allowed out of range pages and they are not normalized
     */
    public function setCurrentPage($currentPage)
    {
        $this->useDeprecatedCurrentPageBooleanArguments(\func_get_args());

        $this->currentPage = $this->filterCurrentPage($currentPage);
        $this->resetForCurrentPageChange();

        return $this;
    }

    private function useDeprecatedCurrentPageBooleanArguments(array $arguments): void
    {
        $this->useDeprecatedCurrentPageAllowOutOfRangePagesBooleanArgument($arguments);
        $this->useDeprecatedCurrentPageNormalizeOutOfRangePagesBooleanArgument($arguments);
    }

    private function useDeprecatedCurrentPageAllowOutOfRangePagesBooleanArgument(array $arguments): void
    {
        $this->useDeprecatedBooleanArgument($arguments, 1, 'setAllowOutOfRangePages', '$allowOutOfRangePages');
    }

    private function useDeprecatedCurrentPageNormalizeOutOfRangePagesBooleanArgument(array $arguments): void
    {
        $this->useDeprecatedBooleanArgument($arguments, 2, 'setNormalizeOutOfRangePages', '$normalizeOutOfRangePages');
    }

    private function useDeprecatedBooleanArgument(array $arguments, int $index, string $method, string $oldArgument): void
    {
        if (isset($arguments[$index])) {
            trigger_deprecation(
                'babdev/pagerfanta',
                '2.2',
                'The %1$s argument of %2$s::setCurrentPage() is deprecated and will no longer be supported in 3.0. Use the %2$s::%3$s() method instead.',
                $oldArgument,
                self::class,
                Pagerfanta::class,
                $method
            );

            $this->$method($arguments[$index]);
        }
    }

    private function filterCurrentPage($currentPage): int
    {
        $currentPage = $this->toInteger($currentPage);
        $this->checkCurrentPage($currentPage);
        $currentPage = $this->filterOutOfRangeCurrentPage($currentPage);

        return $currentPage;
    }

    private function checkCurrentPage($currentPage): void
    {
        if (!\is_int($currentPage)) {
            throw new NotIntegerCurrentPageException();
        }

        if ($currentPage < 1) {
            throw new LessThan1CurrentPageException();
        }
    }

    private function filterOutOfRangeCurrentPage($currentPage): int
    {
        if ($this->notAllowedCurrentPageOutOfRange($currentPage)) {
            return $this->normalizeOutOfRangeCurrentPage($currentPage);
        }

        return $currentPage;
    }

    private function notAllowedCurrentPageOutOfRange(int $currentPage): bool
    {
        return !$this->getAllowOutOfRangePages() &&
               $this->currentPageOutOfRange($currentPage);
    }

    private function currentPageOutOfRange(int $currentPage): bool
    {
        return $currentPage > 1 && $currentPage > $this->getNbPages();
    }

    /**
     * @param int $currentPage
     *
     * @return int
     *
     * @throws OutOfRangeCurrentPageException If the page should not be normalized
     */
    private function normalizeOutOfRangeCurrentPage($currentPage): int
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

    /**
     * Returns the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Returns the results for the current page.
     *
     * @return iterable
     */
    public function getCurrentPageResults()
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

    /**
     * @return iterable
     */
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

    /**
     * Calculates the current page offset start.
     *
     * @return int
     */
    public function getCurrentPageOffsetStart()
    {
        return $this->getNbResults() ?
               $this->calculateOffsetForCurrentPageResults() + 1 :
               0;
    }

    /**
     * Calculates the current page offset end.
     *
     * @return int
     */
    public function getCurrentPageOffsetEnd()
    {
        return $this->hasNextPage() ?
               $this->getCurrentPage() * $this->getMaxPerPage() :
               $this->getNbResults();
    }

    /**
     * Returns the number of results.
     *
     * @return int
     */
    public function getNbResults()
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

    /**
     * Returns the number of pages.
     *
     * @return int
     */
    public function getNbPages()
    {
        $nbPages = $this->calculateNbPages();

        if (0 == $nbPages) {
            return $this->minimumNbPages();
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
     * Returns if the number of results is higher than the max per page.
     *
     * @return bool
     */
    public function haveToPaginate()
    {
        return $this->getNbResults() > $this->maxPerPage;
    }

    /**
     * Returns whether there is previous page or not.
     *
     * @return bool
     */
    public function hasPreviousPage()
    {
        return $this->currentPage > 1;
    }

    /**
     * Returns the previous page.
     *
     * @return int
     *
     * @throws LogicException if there is no previous page
     */
    public function getPreviousPage()
    {
        if (!$this->hasPreviousPage()) {
            throw new LogicException('There is no previous page.');
        }

        return $this->currentPage - 1;
    }

    /**
     * Returns whether there is next page or not.
     *
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->currentPage < $this->getNbPages();
    }

    /**
     * Returns the next page.
     *
     * @return int
     *
     * @throws LogicException if there is no next page
     */
    public function getNextPage()
    {
        if (!$this->hasNextPage()) {
            throw new LogicException('There is no next page.');
        }

        return $this->currentPage + 1;
    }

    /**
     * Implements the \Countable interface.
     *
     * @return int the number of results
     */
    public function count()
    {
        return $this->getNbResults();
    }

    /**
     * Implements the \IteratorAggregate interface.
     *
     * @return \ArrayIterator instance with the current results
     */
    public function getIterator()
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

    /**
     * Implements the \JsonSerializable interface.
     *
     * @return array current page results
     */
    public function jsonSerialize()
    {
        $results = $this->getCurrentPageResults();

        if ($results instanceof \Traversable) {
            return iterator_to_array($results);
        }

        return $results;
    }

    private function toInteger($value)
    {
        if ($this->needsToIntegerConversion($value)) {
            return (int) $value;
        }

        return $value;
    }

    private function needsToIntegerConversion($value): bool
    {
        return (\is_string($value) || \is_float($value)) && (int) $value == $value;
    }

    /**
     * Get page number of the item at specified position (1-based index).
     *
     * @param int $position
     *
     * @return int
     */
    public function getPageNumberForItemAtPosition($position)
    {
        if (!\is_int($position)) {
            throw new NotIntegerException();
        }

        if ($this->getNbResults() < $position) {
            throw new OutOfBoundsException(sprintf('Item requested at position %d, but there are only %d items.', $position, $this->getNbResults()));
        }

        return (int) ceil($position / $this->getMaxPerPage());
    }
}
