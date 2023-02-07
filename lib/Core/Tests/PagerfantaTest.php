<?php declare(strict_types=1);

namespace Pagerfanta\Tests;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\LessThan1MaxPagesException;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PagerfantaTest extends TestCase
{
    /**
     * @var MockObject&AdapterInterface<mixed>
     */
    private MockObject&AdapterInterface $adapter;

    /**
     * @var Pagerfanta<mixed>
     */
    private Pagerfanta $pagerfanta;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->pagerfanta = new Pagerfanta($this->adapter);
    }

    public static function dataCountsAsIntegers(): \Generator
    {
        yield '1 item' => [1];
        yield '10 items' => [10];
        yield '25 items' => [25];
    }

    public static function dataCountsAsNonIntegers(): \Generator
    {
        yield 'float' => [1.1];
        yield 'string float' => ['1.1'];
        yield 'boolean' => [true];
        yield 'array' => [[1]];
    }

    public static function dataLessThan1(): \Generator
    {
        yield 'zero' => [0];
        yield 'negative number' => [-1];
    }

    public function testTheStaticConstructorCreatesAPagerfantaInstance(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage($this->adapter, 2, 5);

        self::assertSame(2, $pagerfanta->getCurrentPage());
        self::assertSame(5, $pagerfanta->getMaxPerPage());
    }

    public function testTheAdapterCanBeRetrieved(): void
    {
        self::assertSame($this->adapter, $this->pagerfanta->getAdapter());
    }

    public function testThePagerCanAllowOutOfRangePages(): void
    {
        self::assertSame($this->pagerfanta, $this->pagerfanta->setAllowOutOfRangePages(true), 'setAllowOutOfRangePages has a fluent interface');
        self::assertTrue($this->pagerfanta->getAllowOutOfRangePages());
    }

    public function testOutOfRangePagesIsDisallowedByDefault(): void
    {
        self::assertFalse($this->pagerfanta->getAllowOutOfRangePages());
    }

    public function testThePagerCanNormalizeOutOfRangePages(): void
    {
        self::assertSame($this->pagerfanta, $this->pagerfanta->setNormalizeOutOfRangePages(true), 'setNormalizeOutOfRangePages has a fluent interface');
        self::assertTrue($this->pagerfanta->getNormalizeOutOfRangePages());
    }

    public function testNormalizingOutOfRangePagesIsDisallowedByDefault(): void
    {
        self::assertFalse($this->pagerfanta->getNormalizeOutOfRangePages());
    }

    /**
     * @phpstan-param positive-int $maxPerPage
     */
    #[DataProvider('dataCountsAsIntegers')]
    public function testTheMaximumNumberOfItemsPerPageCanBeSet(int $maxPerPage): void
    {
        self::assertSame($this->pagerfanta, $this->pagerfanta->setMaxPerPage($maxPerPage), 'setMaxPerPage has a fluent interface');
        self::assertSame($maxPerPage, $this->pagerfanta->getMaxPerPage());
    }

    #[DataProvider('dataLessThan1')]
    public function testSetMaxPerPageShouldThrowExceptionWhenLessThan1(int $maxPerPage): void
    {
        $this->expectException(LessThan1MaxPerPageException::class);

        $this->pagerfanta->setMaxPerPage($maxPerPage);
    }

    public function testSetMaxPerPageAfterCurrentPageShouldThrowExceptionOutOfRange(): void
    {
        $this->expectException(OutOfRangeCurrentPageException::class);

        $this->pagerfanta->setCurrentPage(3);
        $this->pagerfanta->setAllowOutOfRangePages(false);
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(20);
        $this->pagerfanta->setMaxPerPage(10);
    }

    public function testSetMaxPerPageShouldResetCurrentPageResults(): void
    {
        $this->resetCurrentPageResults(function (): void {
            $this->pagerfanta->setMaxPerPage(10);
        });
    }

    public function testSetMaxPerPageShouldNotResetNbResults(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        self::assertSame(100, $this->pagerfanta->getNbResults());
        $this->pagerfanta->setMaxPerPage(5);
        self::assertSame(100, $this->pagerfanta->getNbResults());
    }

    public function testSetMaxPerPageShouldResetNbPages(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        self::assertSame(10, $this->pagerfanta->getNbPages());
        $this->pagerfanta->setMaxPerPage(20);
        self::assertSame(5, $this->pagerfanta->getNbPages());
    }

    public function testTheNumberOfResultsAreRetrievedFromTheAdapter(): void
    {
        $results = 20;

        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn($results);

        self::assertSame($results, $this->pagerfanta->getNbResults());
    }

    public function testGetNbResultsShouldCacheTheNbResultsFromTheAdapter(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(20);

        $this->pagerfanta->getNbResults();
        $this->pagerfanta->getNbResults();
    }

    public function testGetNbPagesShouldCalculateTheNumberOfPages(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(20);

        self::assertSame(5, $this->pagerfanta->getNbPages());
    }

    public function testGetNbPagesShouldRoundUpToTheNextPage(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(15);

        self::assertSame(7, $this->pagerfanta->getNbPages());
    }

    public function testThereShouldBeOnePageWhenThereAreNoResults(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(0);

        self::assertSame(1, $this->pagerfanta->getNbPages());
    }

    public function testTheMaximumNumberPagesCanBeSetAndReset(): void
    {
        // Fake 10 pages being expected
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $originalPageCount = $this->pagerfanta->getNbPages();

        self::assertSame($this->pagerfanta, $this->pagerfanta->setMaxNbPages(5), 'setMaxNbPages has a fluent interface');
        self::assertSame(5, $this->pagerfanta->getNbPages(), 'The configured maximum number of pages should be used');

        self::assertSame($this->pagerfanta, $this->pagerfanta->setMaxNbPages(15), 'setMaxNbPages has a fluent interface');
        self::assertSame($originalPageCount, $this->pagerfanta->getNbPages(), 'When the configured maximum number of pages is less than the real number of pages, then the number of pages should be used');

        self::assertSame($this->pagerfanta, $this->pagerfanta->resetMaxNbPages(), 'resetMaxNbPages has a fluent interface');
        self::assertSame($originalPageCount, $this->pagerfanta->getNbPages(), 'When there is no maximum number of pages configured, then the number of pages should be used');
    }

    #[DataProvider('dataLessThan1')]
    public function testSetMaxNbPagesShouldThrowExceptionWhenLessThan1(int $maxPages): void
    {
        $this->expectException(LessThan1MaxPagesException::class);

        $this->pagerfanta->setMaxNbPages($maxPages);
    }

    /**
     * @phpstan-param positive-int $currentPage
     */
    #[DataProvider('dataCountsAsIntegers')]
    public function testTheCurrentPageNumberCanBeSet(int $currentPage): void
    {
        if ($currentPage > 1) {
            $this->adapter->expects(self::once())
                ->method('getNbResults')
                ->willReturn(100);
        }

        $this->pagerfanta->setMaxPerPage(2);
        self::assertSame($this->pagerfanta, $this->pagerfanta->setCurrentPage($currentPage), 'setCurrentPage has a fluent interface');

        self::assertSame($currentPage, $this->pagerfanta->getCurrentPage());
    }

    #[DataProvider('dataLessThan1')]
    public function testSettingTheCurrentPageShouldThrowExceptionWhenLessThan1(int $currentPage): void
    {
        $this->expectException(LessThan1CurrentPageException::class);

        $this->pagerfanta->setCurrentPage($currentPage);
    }

    public function testSetCurrentPageShouldThrowExceptionWhenThePageIsOutOfRange(): void
    {
        $this->expectException(OutOfRangeCurrentPageException::class);

        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(11);
    }

    public function testSetCurrentPageShouldNotThrowExceptionWhenOutOfRangePagesAreAllowed(): void
    {
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setAllowOutOfRangePages(true);
        $this->pagerfanta->setCurrentPage(11);

        self::assertSame(11, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldResetCurrentPageResults(): void
    {
        $this->resetCurrentPageResults(function (): void {
            $this->pagerfanta->setCurrentPage(1);
        });
    }

    public static function dataGetCurrentPageResultSizes(): \Generator
    {
        // max per page, current page, offset
        yield '10 items per page on page 1' => [10, 1, 0];
        yield '10 items per page on page 2' => [10, 2, 10];
        yield '20 items per page on page 3' => [20, 3, 40];
    }

    /**
     * @phpstan-param positive-int $maxPerPage
     * @phpstan-param positive-int $currentPage
     * @phpstan-param int<0, max>  $offset
     */
    #[DataProvider('dataGetCurrentPageResultSizes')]
    public function testGetCurrentPageResultsShouldReturnASliceFromTheAdapterForTheCurrentPageWithCorrectSizeAndCacheTheResults(int $maxPerPage, int $currentPage, int $offset): void
    {
        if ($currentPage > 1) {
            $this->adapter->expects(self::once())
                ->method('getNbResults')
                ->willReturn(100);
        }

        $this->pagerfanta->setMaxPerPage($maxPerPage);
        $this->pagerfanta->setCurrentPage($currentPage);

        $currentPageResults = new \ArrayObject();

        $this->adapter->expects(self::once())
            ->method('getSlice')
            ->with($offset, $maxPerPage)
            ->willReturn($currentPageResults);

        self::assertSame($currentPageResults, $this->pagerfanta->getCurrentPageResults());
        self::assertSame($currentPageResults, $this->pagerfanta->getCurrentPageResults());
    }

    public function testTheCurrentPageOffsetStartIsRetrieved(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(2);

        self::assertSame(11, $this->pagerfanta->getCurrentPageOffsetStart());
    }

    public function testTheCurrentPageOffsetStartIsRetrievedWhenThereAreNoResults(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(0);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(1);

        self::assertSame(0, $this->pagerfanta->getCurrentPageOffsetStart());
    }

    public function testTheCurrentPageOffsetEndIsRetrieved(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(2);

        self::assertSame(20, $this->pagerfanta->getCurrentPageOffsetEnd());
    }

    public function testTheCurrentPageOffsetEndIsRetrievedWhenOnTheLastPage(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(90);

        $this->pagerfanta->setMaxPerPage(20);
        $this->pagerfanta->setCurrentPage(5);

        self::assertSame(90, $this->pagerfanta->getCurrentPageOffsetEnd());
    }

    public static function dataHaveToPaginate(): \Generator
    {
        yield 'does paginate when number of results is greater than the maximum items per page' => [true, 99, 100];
        yield 'does not paginate when number of results is equal to the maximum items per page' => [false, 100, 100];
        yield 'does not paginate when number of results is less than the maximum items per page' => [false, 100, 99];
    }

    /**
     * @phpstan-param positive-int $maxPerPage
     * @phpstan-param int<0, max>  $nbResults
     */
    #[DataProvider('dataHaveToPaginate')]
    public function testHaveToPaginateReportsCorrectly(bool $expected, int $maxPerPage, int $nbResults): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn($nbResults);

        $this->pagerfanta->setMaxPerPage($maxPerPage);

        self::assertSame($expected, $this->pagerfanta->haveToPaginate());
    }

    public function testHasPreviousPageReportsCorrectly(): void
    {
        $this->adapter->expects(self::atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setCurrentPage(1);
        self::assertFalse($this->pagerfanta->hasPreviousPage());

        for ($page = 2; $page <= $this->pagerfanta->getNbPages(); ++$page) {
            $this->pagerfanta->setCurrentPage($page);
            self::assertTrue($this->pagerfanta->hasPreviousPage());
        }
    }

    public function testGetPreviousPageShouldReturnThePreviousPage(): void
    {
        $this->adapter->expects(self::atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        for ($page = 2; $page <= $this->pagerfanta->getNbPages(); ++$page) {
            $this->pagerfanta->setCurrentPage($page);
            self::assertSame($page - 1, $this->pagerfanta->getPreviousPage());
        }
    }

    public function testGetPreviousPageShouldThrowALogicExceptionIfThereIsNoPreviousPage(): void
    {
        $this->expectException(LogicException::class);

        $this->pagerfanta->getPreviousPage();
    }

    public function testHasNextPageReportsCorrectly(): void
    {
        $this->adapter->expects(self::atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        for ($page = 1; $page < $this->pagerfanta->getNbPages(); ++$page) {
            $this->pagerfanta->setCurrentPage($page);
            self::assertTrue($this->pagerfanta->hasNextPage());
        }

        $this->pagerfanta->setCurrentPage($this->pagerfanta->getNbPages());
        self::assertFalse($this->pagerfanta->hasNextPage());
    }

    public function testGetNextPageShouldReturnTheNextPage(): void
    {
        $this->adapter->expects(self::atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        for ($page = 1; $page < $this->pagerfanta->getNbPages(); ++$page) {
            $this->pagerfanta->setCurrentPage($page);
            self::assertSame($page + 1, $this->pagerfanta->getNextPage());
        }
    }

    public function testGetNextPageShouldThrowALogicExceptionIfTheCurrentPageIsTheLast(): void
    {
        $this->expectException(LogicException::class);

        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setCurrentPage($this->pagerfanta->getNbPages());

        $this->pagerfanta->getNextPage();
    }

    public function testThePagerCanBeCounted(): void
    {
        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        self::assertCount(100, $this->pagerfanta);
    }

    public function testThePagerCanBeIteratedWithTheCurrentPageResultsWhenTheAdapterReturnsAnIterator(): void
    {
        /** @var MockObject&\Iterator $currentPageResults */
        $currentPageResults = $this->createMock(\Iterator::class);

        $this->adapter->expects(self::once())
            ->method('getSlice')
            ->willReturn($currentPageResults);

        self::assertSame($currentPageResults, $this->pagerfanta->getIterator());
    }

    public function testThePagerCanBeIteratedWithTheCurrentPageResultsWhenTheAdapterReturnsAnIteratorAggregate(): void
    {
        $iterator = new \ArrayIterator(['foo']);

        /** @var MockObject&\IteratorAggregate $currentPageResults */
        $currentPageResults = $this->createMock(\IteratorAggregate::class);
        $currentPageResults->expects(self::once())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->adapter->expects(self::once())
            ->method('getSlice')
            ->willReturn($currentPageResults);

        self::assertSame($iterator, $this->pagerfanta->getIterator());
    }

    public function testThePagerCanBeIteratedWithTheCurrentPageResultsWhenTheAdapterReturnsAnArray(): void
    {
        $this->adapter->expects(self::once())
            ->method('getSlice')
            ->willReturn([]);

        self::assertInstanceOf(\ArrayIterator::class, $this->pagerfanta->getIterator());
    }

    public function testThePagerCanBeJsonEncodedWithTheCurrentPageResultsWhenTheAdapterReturnsAnArray(): void
    {
        $pageResults = ['foo', 'bar'];

        $this->adapter->expects(self::once())
            ->method('getSlice')
            ->willReturn($pageResults);

        self::assertJsonStringEqualsJsonString(json_encode($pageResults, \JSON_THROW_ON_ERROR), json_encode($this->pagerfanta, \JSON_THROW_ON_ERROR));
    }

    public function testThePagerCanBeJsonEncodedWithTheCurrentPageResultsWhenTheAdapterReturnsATraversable(): void
    {
        $pageResults = ['foo', 'bar'];

        $iterator = new \ArrayIterator($pageResults);

        /** @var MockObject&\IteratorAggregate $currentPageResults */
        $currentPageResults = $this->createMock(\IteratorAggregate::class);
        $currentPageResults->expects(self::once())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->adapter->expects(self::once())
            ->method('getSlice')
            ->willReturn($currentPageResults);

        self::assertJsonStringEqualsJsonString(json_encode($pageResults, \JSON_THROW_ON_ERROR), json_encode($this->pagerfanta, \JSON_THROW_ON_ERROR));
    }

    public static function dataGetPageNumberForItemAtPosition(): \Generator
    {
        yield 'position 10' => [1, 10];
        yield 'position 11' => [2, 11];
    }

    /**
     * @phpstan-param positive-int $position
     */
    #[DataProvider('dataGetPageNumberForItemAtPosition')]
    public function testGetPageNumberForItemAtPosition(int $page, int $position): void
    {
        $this->adapter->expects(self::atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        self::assertSame($page, $this->pagerfanta->getPageNumberForItemAtPosition($position));
    }

    public function testGetPageNumberForItemAtPositionShouldThrowAnExceptionIfTheItemIsMoreThanNbPages(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $this->adapter->expects(self::once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->getPageNumberForItemAtPosition(101);
    }

    private function resetCurrentPageResults(callable $callback): void
    {
        $this->pagerfanta->setMaxPerPage(10);

        $currentPageResults0 = new \ArrayObject();
        $currentPageResults1 = new \ArrayObject();

        $this->adapter->expects(self::exactly(2))
            ->method('getSlice')
            ->willReturnOnConsecutiveCalls(
                $currentPageResults0,
                $currentPageResults1
            );

        self::assertSame($currentPageResults0, $this->pagerfanta->getCurrentPageResults());
        $callback();
        self::assertSame($currentPageResults1, $this->pagerfanta->getCurrentPageResults());
    }
}
