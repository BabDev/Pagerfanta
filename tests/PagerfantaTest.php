<?php declare(strict_types=1);

namespace Pagerfanta\Tests;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\LessThan1MaxPagesException;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Exception\NotBooleanException;
use Pagerfanta\Exception\NotIntegerCurrentPageException;
use Pagerfanta\Exception\NotIntegerException;
use Pagerfanta\Exception\NotIntegerMaxPerPageException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PagerfantaTest extends TestCase
{
    /**
     * @var MockObject|AdapterInterface
     */
    private $adapter;

    /**
     * @var Pagerfanta
     */
    private $pagerfanta;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->pagerfanta = new Pagerfanta($this->adapter);
    }

    public function dataCountsAsIntegers(): \Generator
    {
        yield '1 item' => [1];
        yield '10 items' => [10];
        yield '25 items' => [25];
    }

    public function dataCountsAsNonIntegers(): \Generator
    {
        yield 'float' => [1.1];
        yield 'string float' => ['1.1'];
        yield 'boolean' => [true];
        yield 'array' => [[1]];
    }

    public function dataCountsAsStrings(): \Generator
    {
        yield '1 item as string' => ['1'];
        yield '10 items as string' => ['10'];
        yield '25 items as string' => ['25'];
    }

    public function dataLessThan1(): \Generator
    {
        yield 'zero' => [0];
        yield 'negative number' => [-1];
    }

    public function dataNotBoolean(): \Generator
    {
        yield 'integer' => [1];
        yield 'string' => ['1'];
        yield 'float' => [1.1];
    }

    public function testTheAdapterCanBeRetrieved(): void
    {
        $this->assertSame($this->adapter, $this->pagerfanta->getAdapter());
    }

    public function testThePagerCanAllowOutOfRangePages(): void
    {
        $this->assertSame($this->pagerfanta, $this->pagerfanta->setAllowOutOfRangePages(true), 'setAllowOutOfRangePages has a fluent interface');
        $this->assertTrue($this->pagerfanta->getAllowOutOfRangePages());
    }

    /**
     * @param mixed $value
     *
     * @dataProvider dataNotBoolean
     */
    public function testSettingOutOfRangePagesRejectsNonBooleanValues($value): void
    {
        $this->expectException(NotBooleanException::class);

        $this->pagerfanta->setAllowOutOfRangePages($value);
    }

    public function testOutOfRangePagesIsDisallowedByDefault(): void
    {
        $this->assertFalse($this->pagerfanta->getAllowOutOfRangePages());
    }

    public function testThePagerCanNormalizeOutOfRangePages(): void
    {
        $this->assertSame($this->pagerfanta, $this->pagerfanta->setNormalizeOutOfRangePages(true), 'setNormalizeOutOfRangePages has a fluent interface');
        $this->assertTrue($this->pagerfanta->getNormalizeOutOfRangePages());
    }

    /**
     * @param mixed $value
     *
     * @dataProvider dataNotBoolean
     */
    public function testNormalizingOutOfRangePagesRejectsNonBooleanValues($value): void
    {
        $this->expectException(NotBooleanException::class);

        $this->pagerfanta->setNormalizeOutOfRangePages($value);
    }

    public function testNormalizingOutOfRangePagesIsDisallowedByDefault(): void
    {
        $this->assertFalse($this->pagerfanta->getNormalizeOutOfRangePages());
    }

    /**
     * @param int|string $maxPerPage
     *
     * @dataProvider dataCountsAsIntegers
     * @dataProvider dataCountsAsStrings
     */
    public function testTheMaximumNumberOfItemsPerPageCanBeSet($maxPerPage): void
    {
        $this->assertSame($this->pagerfanta, $this->pagerfanta->setMaxPerPage($maxPerPage), 'setMaxPerPage has a fluent interface');
        $this->assertSame((int) $maxPerPage, $this->pagerfanta->getMaxPerPage());
    }

    /**
     * @param mixed $maxPerPage
     *
     * @dataProvider dataCountsAsNonIntegers
     */
    public function testTheMaximumNumberOfItemsPerPageCannotBeSetWithNonIntegerValues($maxPerPage): void
    {
        $this->expectException(NotIntegerMaxPerPageException::class);

        $this->pagerfanta->setMaxPerPage($maxPerPage);
    }

    /**
     * @dataProvider dataLessThan1
     */
    public function testSetMaxPerPageShouldThrowExceptionWhenLessThan1(int $maxPerPage): void
    {
        $this->expectException(LessThan1MaxPerPageException::class);

        $this->pagerfanta->setMaxPerPage($maxPerPage);
    }

    public function testSetMaxPerPageShouldResetCurrentPageResults(): void
    {
        $this->resetCurrentPageResults(function (): void {
            $this->pagerfanta->setMaxPerPage(10);
        });
    }

    public function testSetMaxPerPageShouldResetNbResults(): void
    {
        $this->prepareForResetNbResults();

        $this->assertSame(100, $this->pagerfanta->getNbResults());
        $this->pagerfanta->setMaxPerPage(10);
        $this->assertSame(50, $this->pagerfanta->getNbResults());
    }

    public function testSetMaxPerPageShouldResetNbPages(): void
    {
        $this->prepareForResetNbResults();

        $this->assertSame(10, $this->pagerfanta->getNbPages());
        $this->pagerfanta->setMaxPerPage(10);
        $this->assertSame(5, $this->pagerfanta->getNbPages());
    }

    public function testTheNumberOfResultsAreRetrievedFromTheAdapter(): void
    {
        $results = 20;

        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn($results);

        $this->assertSame($results, $this->pagerfanta->getNbResults());
    }

    public function testGetNbResultsShouldCacheTheNbResultsFromTheAdapter(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(20);

        $this->pagerfanta->getNbResults();
        $this->pagerfanta->getNbResults();
    }

    public function testGetNbPagesShouldCalculateTheNumberOfPages(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(20);

        $this->assertSame(5, $this->pagerfanta->getNbPages());
    }

    public function testGetNbPagesShouldRoundUpToTheNextPage(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(15);

        $this->assertSame(7, $this->pagerfanta->getNbPages());
    }

    public function testThereShouldBeOnePageWhenThereAreNoResults(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(0);

        $this->assertSame(1, $this->pagerfanta->getNbPages());
    }

    public function testTheMaximumNumberPagesCanBeSet(): void
    {
        $this->assertSame($this->pagerfanta, $this->pagerfanta->setMaxNbPages(10), 'setMaxNbPages has a fluent interface');
        $this->assertTrue($this->pagerfanta->getNbPages() <= 10);
    }

    /**
     * @dataProvider dataLessThan1
     */
    public function testSetMaxNbPagesShouldThrowExceptionWhenLessThan1(int $maxPages): void
    {
        $this->expectException(LessThan1MaxPagesException::class);

        $this->pagerfanta->setMaxNbPages($maxPages);
    }

    /**
     * @param int|string $currentPage
     *
     * @dataProvider dataCountsAsIntegers
     * @dataProvider dataCountsAsStrings
     */
    public function testTheCurrentPageNumberCanBeSet($currentPage): void
    {
        if ((int) $currentPage > 1) {
            $this->adapter->expects($this->once())
                ->method('getNbResults')
                ->willReturn(100);
        }

        $this->pagerfanta->setMaxPerPage(2);
        $this->assertSame($this->pagerfanta, $this->pagerfanta->setCurrentPage($currentPage), 'setCurrentPage has a fluent interface');

        $this->assertSame((int) $currentPage, $this->pagerfanta->getCurrentPage());
    }

    /**
     * @param mixed $currentPage
     *
     * @dataProvider dataCountsAsNonIntegers
     */
    public function testTheCurrentPageNumberCannotBeSetWithNonIntegerValues($currentPage): void
    {
        $this->expectException(NotIntegerCurrentPageException::class);

        $this->pagerfanta->setCurrentPage($currentPage);
    }

    /**
     * @dataProvider dataLessThan1
     */
    public function testSettingTheCurrentPageShouldThrowExceptionWhenLessThan1(int $currentPage): void
    {
        $this->expectException(LessThan1CurrentPageException::class);

        $this->pagerfanta->setCurrentPage($currentPage);
    }

    public function testSetCurrentPageShouldThrowExceptionWhenThePageIsOutOfRange(): void
    {
        $this->expectException(OutOfRangeCurrentPageException::class);

        $this->adapter->expects($this->once())
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

        $this->assertSame(11, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldNotThrowExceptionWhenOutOfRangePagesAreAllowedWithTheDeprecatedMethodSignature(): void
    {
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(11, true);

        $this->assertSame(11, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldNormalizeThePageWhenOutOfRangeAndNormalizationIsAllowed(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setAllowOutOfRangePages(false);
        $this->pagerfanta->setNormalizeOutOfRangePages(true);
        $this->pagerfanta->setCurrentPage(11);

        $this->assertSame(10, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldNotThrowExceptionWhenOutOfRangeAndNormalizationIsAllowedWithTheDeprecatedMethodSignature(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(11, false, true);

        $this->assertSame(10, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldResetCurrentPageResults(): void
    {
        $this->resetCurrentPageResults(function (): void {
            $this->pagerfanta->setCurrentPage(1);
        });
    }

    public function dataGetCurrentPageResultSizes(): \Generator
    {
        // max per page, current page, offset
        yield '10 items per page on page 1' => [10, 1, 0];
        yield '10 items per page on page 2' => [10, 2, 10];
        yield '20 items per page on page 3' => [20, 3, 40];
    }

    /**
     * @dataProvider dataGetCurrentPageResultSizes
     */
    public function testGetCurrentPageResultsShouldReturnASliceFromTheAdapterForTheCurrentPageWithCorrectSizeAndCacheTheResults(int $maxPerPage, int $currentPage, int $offset): void
    {
        if ($currentPage > 1) {
            $this->adapter->expects($this->once())
                ->method('getNbResults')
                ->willReturn(100);
        }

        $this->pagerfanta->setMaxPerPage($maxPerPage);
        $this->pagerfanta->setCurrentPage($currentPage);

        $currentPageResults = new \ArrayObject();

        $this->adapter->expects($this->once())
            ->method('getSlice')
            ->with($offset, $maxPerPage)
            ->willReturn($currentPageResults);

        $this->assertSame($currentPageResults, $this->pagerfanta->getCurrentPageResults());
        $this->assertSame($currentPageResults, $this->pagerfanta->getCurrentPageResults());
    }

    public function testTheCurrentPageOffsetStartIsRetrieved(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(2);

        $this->assertSame(11, $this->pagerfanta->getCurrentPageOffsetStart());
    }

    public function testTheCurrentPageOffsetStartIsRetrievedWhenThereAreNoResults(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(0);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(1);

        $this->assertSame(0, $this->pagerfanta->getCurrentPageOffsetStart());
    }

    public function testTheCurrentPageOffsetEndIsRetrieved(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(2);

        $this->assertSame(20, $this->pagerfanta->getCurrentPageOffsetEnd());
    }

    public function testTheCurrentPageOffsetEndIsRetrievedWhenOnTheLastPage(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(90);

        $this->pagerfanta->setMaxPerPage(20);
        $this->pagerfanta->setCurrentPage(5);

        $this->assertSame(90, $this->pagerfanta->getCurrentPageOffsetEnd());
    }

    public function dataHaveToPaginate(): \Generator
    {
        yield 'does paginate when number of results is greater than the maximum items per page' => [true, 99, 100];
        yield 'does not paginate when number of results is equal to the maximum items per page' => [false, 100, 100];
        yield 'does not paginate when number of results is less than the maximum items per page' => [false, 100, 99];
    }

    /**
     * @dataProvider dataHaveToPaginate
     */
    public function testHaveToPaginateReportsCorrectly(bool $expected, int $maxPerPage, int $nbResults): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn($nbResults);

        $this->pagerfanta->setMaxPerPage($maxPerPage);

        $this->assertSame($expected, $this->pagerfanta->haveToPaginate());
    }

    public function testHasPreviousPageReportsCorrectly(): void
    {
        $this->adapter->expects($this->atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setCurrentPage(1);
        $this->assertFalse($this->pagerfanta->hasPreviousPage());

        for ($page = 2; $page <= $this->pagerfanta->getNbPages(); ++$page) {
            $this->pagerfanta->setCurrentPage($page);
            $this->assertTrue($this->pagerfanta->hasPreviousPage());
        }
    }

    public function testGetPreviousPageShouldReturnThePreviousPage(): void
    {
        $this->adapter->expects($this->atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        for ($page = 2; $page <= $this->pagerfanta->getNbPages(); ++$page) {
            $this->pagerfanta->setCurrentPage($page);
            $this->assertSame($page - 1, $this->pagerfanta->getPreviousPage());
        }
    }

    public function testGetPreviousPageShouldThrowALogicExceptionIfThereIsNoPreviousPage(): void
    {
        $this->expectException(LogicException::class);

        $this->pagerfanta->getPreviousPage();
    }

    public function testHasNextPageReportsCorrectly(): void
    {
        $this->adapter->expects($this->atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        for ($page = 1; $page < $this->pagerfanta->getNbPages(); ++$page) {
            $this->pagerfanta->setCurrentPage($page);
            $this->assertTrue($this->pagerfanta->hasNextPage());
        }

        $this->pagerfanta->setCurrentPage($this->pagerfanta->getNbPages());
        $this->assertFalse($this->pagerfanta->hasNextPage());
    }

    public function testGetNextPageShouldReturnTheNextPage(): void
    {
        $this->adapter->expects($this->atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        for ($page = 1; $page < $this->pagerfanta->getNbPages(); ++$page) {
            $this->pagerfanta->setCurrentPage($page);
            $this->assertSame($page + 1, $this->pagerfanta->getNextPage());
        }
    }

    public function testGetNextPageShouldThrowALogicExceptionIfTheCurrentPageIsTheLast(): void
    {
        $this->expectException(LogicException::class);

        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->setCurrentPage($this->pagerfanta->getNbPages());

        $this->pagerfanta->getNextPage();
    }

    public function testThePagerCanBeCounted(): void
    {
        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->assertCount(100, $this->pagerfanta);
    }

    public function testThePagerCanBeIteratedWithTheCurrentPageResultsWhenTheAdapterReturnsAnIterator(): void
    {
        /** @var MockObject|\Iterator $currentPageResults */
        $currentPageResults = $this->createMock(\Iterator::class);

        $this->adapter->expects($this->once())
            ->method('getSlice')
            ->willReturn($currentPageResults);

        $this->assertSame($currentPageResults, $this->pagerfanta->getIterator());
    }

    public function testThePagerCanBeIteratedWithTheCurrentPageResultsWhenTheAdapterReturnsAnIteratorAggregate(): void
    {
        $iterator = new \ArrayIterator(['foo']);

        /** @var MockObject|\IteratorAggregate $currentPageResults */
        $currentPageResults = $this->createMock(\IteratorAggregate::class);
        $currentPageResults->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->adapter->expects($this->once())
            ->method('getSlice')
            ->willReturn($currentPageResults);

        $this->assertSame($iterator, $this->pagerfanta->getIterator());
    }

    public function testThePagerCanBeIteratedWithTheCurrentPageResultsWhenTheAdapterReturnsAnArray(): void
    {
        $this->adapter->expects($this->once())
            ->method('getSlice')
            ->willReturn([]);

        $this->assertInstanceOf(\ArrayIterator::class, $this->pagerfanta->getIterator());
    }

    public function testThePagerCanBeJsonEncodedWithTheCurrentPageResultsWhenTheAdapterReturnsAnArray(): void
    {
        $pageResults = ['foo', 'bar'];

        $this->adapter->expects($this->once())
            ->method('getSlice')
            ->willReturn($pageResults);

        $this->assertJsonStringEqualsJsonString(json_encode($pageResults), json_encode($this->pagerfanta));
    }

    public function testThePagerCanBeJsonEncodedWithTheCurrentPageResultsWhenTheAdapterReturnsATraversable(): void
    {
        $pageResults = ['foo', 'bar'];

        $iterator = new \ArrayIterator($pageResults);

        /** @var MockObject|\IteratorAggregate $currentPageResults */
        $currentPageResults = $this->createMock(\IteratorAggregate::class);
        $currentPageResults->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->adapter->expects($this->once())
            ->method('getSlice')
            ->willReturn($currentPageResults);

        $this->assertJsonStringEqualsJsonString(json_encode($pageResults), json_encode($this->pagerfanta));
    }

    public function dataGetPageNumberForItemAtPosition(): \Generator
    {
        yield 'position 10' => [1, 10];
        yield 'position 11' => [2, 11];
    }

    /**
     * @dataProvider dataGetPageNumberForItemAtPosition
     */
    public function testGetPageNumberForItemAtPosition(int $page, int $position): void
    {
        $this->adapter->expects($this->atLeastOnce())
            ->method('getNbResults')
            ->willReturn(100);

        $this->assertSame($page, $this->pagerfanta->getPageNumberForItemAtPosition($position));
    }

    public function testGetPageNumberForItemAtPositionShouldThrowANotIntegerItemExceptionIfTheItemIsNotAnInteger(): void
    {
        $this->expectException(NotIntegerException::class);

        $this->pagerfanta->getPageNumberForItemAtPosition('foo');
    }

    public function testGetPageNumberForItemAtPositionShouldThrowAnExceptionIfTheItemIsMoreThanNbPages(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(100);

        $this->pagerfanta->getPageNumberForItemAtPosition(101);
    }

    private function prepareForResetNbResults(): void
    {
        $this->pagerfanta->setMaxPerPage(10);

        $this->adapter->expects($this->exactly(2))
            ->method('getNbResults')
            ->willReturnOnConsecutiveCalls(100, 50);
    }

    private function resetCurrentPageResults(callable $callback): void
    {
        $this->pagerfanta->setMaxPerPage(10);

        $currentPageResults0 = new \ArrayObject();
        $currentPageResults1 = new \ArrayObject();

        $this->adapter->expects($this->exactly(2))
            ->method('getSlice')
            ->willReturnOnConsecutiveCalls(
                $currentPageResults0,
                $currentPageResults1
            );

        $this->assertSame($currentPageResults0, $this->pagerfanta->getCurrentPageResults());
        $callback();
        $this->assertSame($currentPageResults1, $this->pagerfanta->getCurrentPageResults());
    }
}
