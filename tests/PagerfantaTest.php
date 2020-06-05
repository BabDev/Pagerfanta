<?php declare(strict_types=1);

namespace Pagerfanta\Tests;

use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;

class IteratorAggregate implements \IteratorAggregate
{
    private $iterator;

    public function __construct()
    {
        $this->iterator = new \ArrayIterator(['ups']);
    }

    public function getIterator()
    {
        return $this->iterator;
    }
}

class PagerfantaTest extends TestCase
{
    private $adapter;
    /**
     * @var Pagerfanta
     */
    private $pagerfanta;

    protected function setUp(): void
    {
        $this->adapter = $this->getMockBuilder('Pagerfanta\Adapter\AdapterInterface')->getMock();
        $this->pagerfanta = new Pagerfanta($this->adapter);
    }

    private function setAdapterNbResultsAny($nbResults): void
    {
        $this->setAdapterNbResults($this->any(), $nbResults);
    }

    private function setAdapterNbResultsOnce($nbResults): void
    {
        $this->setAdapterNbResults($this->once(), $nbResults);
    }

    private function setAdapterNbResults($expects, $nbResults): void
    {
        $this->adapter
            ->expects($expects)
            ->method('getNbResults')
            ->willReturn($nbResults);
    }

    public function testGetAdapterShouldReturnTheAdapter(): void
    {
        $this->assertSame($this->adapter, $this->pagerfanta->getAdapter());
    }

    public function testGetAllowOutOfRangePagesShouldBeFalseByDefault(): void
    {
        $this->assertFalse($this->pagerfanta->getAllowOutOfRangePages());
    }

    public function testSetAllowOutOfRangePagesShouldSetTrue(): void
    {
        $this->pagerfanta->setAllowOutOfRangePages(true);
        $this->assertTrue($this->pagerfanta->getAllowOutOfRangePages());
    }

    public function testSetAllowOutOfRangePagesShouldSetFalse(): void
    {
        $this->pagerfanta->setAllowOutOfRangePages(false);
        $this->assertFalse($this->pagerfanta->getAllowOutOfRangePages());
    }

    public function testSetAllowOutOfRangePagesShouldReturnThePagerfanta(): void
    {
        $this->assertSame($this->pagerfanta, $this->pagerfanta->setAllowOutOfRangePages(true));
    }

    /**
     * @dataProvider notBooleanProvider
     */
    public function testSetAllowOutOfRangePagesShouldThrowNotBooleanExceptionWhenNotBoolean($value): void
    {
        $this->expectException(\Pagerfanta\Exception\NotBooleanException::class);

        $this->pagerfanta->setAllowOutOfRangePages($value);
    }

    public function testGetNormalizeOutOfRangePagesShouldBeFalseByDefault(): void
    {
        $this->assertFalse($this->pagerfanta->getNormalizeOutOfRangePages());
    }

    public function testSetNormalizeOutOfRangePagesShouldSetTrue(): void
    {
        $this->pagerfanta->setNormalizeOutOfRangePages(true);
        $this->assertTrue($this->pagerfanta->getNormalizeOutOfRangePages());
    }

    public function testSetNormalizeOutOfRangePagesShouldSetFalse(): void
    {
        $this->pagerfanta->setNormalizeOutOfRangePages(false);
        $this->assertFalse($this->pagerfanta->getNormalizeOutOfRangePages());
    }

    public function testSetNormalizeOutOfRangePagesShouldReturnThePagerfanta(): void
    {
        $this->assertSame($this->pagerfanta, $this->pagerfanta->setNormalizeOutOfRangePages(true));
    }

    /**
     * @dataProvider notBooleanProvider
     */
    public function testSetNormalizeOutOfRangePagesShouldThrowNotBooleanExceptionWhenNotBoolean($value): void
    {
        $this->expectException(\Pagerfanta\Exception\NotBooleanException::class);

        $this->pagerfanta->setNormalizeOutOfRangePages($value);
    }

    public function notBooleanProvider()
    {
        return [
            [1],
            ['1'],
            [1.1],
        ];
    }

    /**
     * @dataProvider setMaxPerPageShouldSetAnIntegerProvider
     */
    public function testSetMaxPerPageShouldSetAnInteger($maxPerPage): void
    {
        $this->pagerfanta->setMaxPerPage($maxPerPage);

        $this->assertSame($maxPerPage, $this->pagerfanta->getMaxPerPage());
    }

    public function setMaxPerPageShouldSetAnIntegerProvider()
    {
        return [
            [1],
            [10],
            [25],
        ];
    }

    /**
     * @dataProvider setMaxPerPageShouldConvertStringsToIntegersProvider
     */
    public function testSetMaxPerPageShouldConvertStringsToIntegers($maxPerPage): void
    {
        $this->pagerfanta->setMaxPerPage($maxPerPage);
        $this->assertSame((int) $maxPerPage, $this->pagerfanta->getMaxPerPage());
    }

    public function setMaxPerPageShouldConvertStringsToIntegersProvider()
    {
        return [
            ['1'],
            ['10'],
            ['25'],
        ];
    }

    public function testSetMaxPerPageShouldReturnThePagerfanta(): void
    {
        $this->assertSame($this->pagerfanta, $this->pagerfanta->setMaxPerPage(10));
    }

    /**
     * @dataProvider      setMaxPerPageShouldThrowExceptionWhenInvalidProvider
     */
    public function testSetMaxPerPageShouldThrowExceptionWhenInvalid($maxPerPage): void
    {
        $this->expectException(\Pagerfanta\Exception\NotIntegerMaxPerPageException::class);

        $this->pagerfanta->setMaxPerPage($maxPerPage);
    }

    public function setMaxPerPageShouldThrowExceptionWhenInvalidProvider()
    {
        return [
            [1.1],
            ['1.1'],
            [true],
            [[1]],
        ];
    }

    /**
     * @dataProvider      setMaxPerPageShouldThrowExceptionWhenLessThan1Provider
     */
    public function testSetMaxPerPageShouldThrowExceptionWhenLessThan1($maxPerPage): void
    {
        $this->expectException(\Pagerfanta\Exception\LessThan1MaxPerPageException::class);

        $this->pagerfanta->setMaxPerPage($maxPerPage);
    }

    public function setMaxPerPageShouldThrowExceptionWhenLessThan1Provider()
    {
        return [
            [0],
            [-1],
        ];
    }

    public function testSetMaxPerPageShouldResetCurrentPageResults(): void
    {
        $pagerfanta = $this->pagerfanta;

        $this->assertResetCurrentPageResults(function () use ($pagerfanta): void {
            $pagerfanta->setMaxPerPage(10);
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

    private function prepareForResetNbResults(): void
    {
        $this->pagerfanta->setMaxPerPage(10);

        $this->adapter
            ->expects($this->at(0))
            ->method('getNbResults')
            ->willReturn(100);
        $this->adapter
            ->expects($this->at(1))
            ->method('getNbResults')
            ->willReturn(50);
    }

    public function testGetNbResultsShouldReturnTheNbResultsFromTheAdapter(): void
    {
        $this->setAdapterNbResultsAny(20);

        $this->assertSame(20, $this->pagerfanta->getNbResults());
    }

    public function testGetNbResultsShouldCacheTheNbResultsFromTheAdapter(): void
    {
        $this->setAdapterNbResultsOnce(20);

        $this->pagerfanta->getNbResults();
        $this->pagerfanta->getNbResults();
    }

    public function testGetNbPagesShouldCalculateTheNumberOfPages(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(20);

        $this->assertSame(5, $this->pagerfanta->getNbPages());
    }

    public function testGetNbPagesShouldRoundToUp(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(15);

        $this->assertSame(7, $this->pagerfanta->getNbPages());
    }

    public function testGetNbPagesShouldReturn1WhenThereAreNoResults(): void
    {
        $this->setAdapterNbResultsAny(0);

        $this->assertSame(1, $this->pagerfanta->getNbPages());
    }

    /**
     * @dataProvider setCurrentPageShouldSetAnIntegerProvider
     */
    public function testSetCurrentPageShouldSetAnInteger($currentPage): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(2);
        $this->pagerfanta->setCurrentPage($currentPage);

        $this->assertSame($currentPage, $this->pagerfanta->getCurrentPage());
    }

    public function setCurrentPageShouldSetAnIntegerProvider()
    {
        return [
            [1],
            [10],
            [25],
        ];
    }

    /**
     * @dataProvider setCurrentPageShouldConvertStringsToIntegersProvider
     */
    public function testSetCurrentPageShouldConvertStringsToIntegers($currentPage): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(2);
        $this->pagerfanta->setCurrentPage($currentPage);

        $this->assertSame((int) $currentPage, $this->pagerfanta->getCurrentPage());
    }

    public function setCurrentPageShouldConvertStringsToIntegersProvider()
    {
        return [
            ['1'],
            ['10'],
            ['25'],
        ];
    }

    /**
     * @dataProvider      setCurrentPageShouldThrowExceptionWhenInvalidProvider
     */
    public function testSetCurrentPageShouldThrowExceptionWhenInvalid($currentPage): void
    {
        $this->expectException(\Pagerfanta\Exception\NotIntegerCurrentPageException::class);

        $this->pagerfanta->setCurrentPage($currentPage);
    }

    public function setCurrentPageShouldThrowExceptionWhenInvalidProvider()
    {
        return [
            [1.1],
            ['1.1'],
            [true],
            [[1]],
        ];
    }

    /**
     * @dataProvider      setCurrentPageShouldThrowExceptionWhenLessThan1Provider
     */
    public function testCurrentPagePageShouldThrowExceptionWhenLessThan1($currentPage): void
    {
        $this->expectException(\Pagerfanta\Exception\LessThan1CurrentPageException::class);

        $this->pagerfanta->setCurrentPage($currentPage);
    }

    public function setCurrentPageShouldThrowExceptionWhenLessThan1Provider()
    {
        return [
            [0],
            [-1],
        ];
    }

    public function testSetCurrentPageShouldThrowExceptionWhenThePageIsOutOfRange(): void
    {
        $this->expectException(\Pagerfanta\Exception\OutOfRangeCurrentPageException::class);

        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(11);
    }

    public function testSetCurrentPageShouldNotThrowExceptionWhenIndicatingAllowOurOfRangePages(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setAllowOutOfRangePages(true);
        $this->pagerfanta->setCurrentPage(11);

        $this->assertSame(11, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldNotThrowExceptionWhenIndicatingAllowOurOfRangePagesWithOldBooleanArguments(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(11, true);

        $this->assertSame(11, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldReturnThePagerfanta(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);

        $this->assertSame($this->pagerfanta, $this->pagerfanta->setCurrentPage(1));
    }

    public function testSetCurrentPageShouldNormalizePageWhenOutOfRangePageAndIndicatingNormalizeOutOfRangePages(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setAllowOutOfRangePages(false);
        $this->pagerfanta->setNormalizeOutOfRangePages(true);
        $this->pagerfanta->setCurrentPage(11);

        $this->assertSame(10, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldNormalizePageWhenOutOfRangePageAndIndicatingNormalizeOutOfRangePagesWithDeprecatedBooleansArguments(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(11, false, true);

        $this->assertSame(10, $this->pagerfanta->getCurrentPage());
    }

    public function testSetCurrentPageShouldResetCurrentPageResults(): void
    {
        $pagerfanta = $this->pagerfanta;

        $this->assertResetCurrentPageResults(function () use ($pagerfanta): void {
            $pagerfanta->setCurrentPage(1);
        });
    }

    /**
     * @dataProvider testGetCurrentPageResultsShouldReturnASliceFromTheAdapterDependingOnTheCurrentPageAndMaxPerPageProvider
     */
    public function testGetCurrentPageResultsShouldReturnASliceFromTheAdapterDependingOnTheCurrentPageAndMaxPerPage($maxPerPage, $currentPage, $offset): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage($maxPerPage);
        $this->pagerfanta->setCurrentPage($currentPage);

        $currentPageResults = new \ArrayObject();

        $this->adapter
            ->expects($this->any())
            ->method('getSlice')
            ->with($this->equalTo($offset), $this->equalTo($maxPerPage))
            ->willReturn($currentPageResults);

        $this->assertSame($currentPageResults, $this->pagerfanta->getCurrentPageResults());
    }

    public function testGetCurrentPageResultsShouldReturnASliceFromTheAdapterDependingOnTheCurrentPageAndMaxPerPageProvider()
    {
        // max per page, current page, offset
        return [
            [10, 1, 0],
            [10, 2, 10],
            [20, 3, 40],
        ];
    }

    public function testGetCurrentPageResultsShouldCacheTheResults(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(1);

        $currentPageResults = new \ArrayObject();

        $this->adapter
            ->expects($this->once())
            ->method('getSlice')
            ->willReturn($currentPageResults);

        $this->pagerfanta->getCurrentPageResults();
        $this->assertSame($currentPageResults, $this->pagerfanta->getCurrentPageResults());
    }

    public function testGetCurrentPageOffsetStart(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(2);

        $this->assertSame(11, $this->pagerfanta->getCurrentPageOffsetStart());
    }

    public function testGetCurrentPageOffsetStartWith0NbResults(): void
    {
        $this->setAdapterNbResultsAny(0);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(1);

        $this->assertSame(0, $this->pagerfanta->getCurrentPageOffsetStart());
    }

    public function testGetCurrentPageOffsetEnd(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(2);

        $this->assertSame(20, $this->pagerfanta->getCurrentPageOffsetEnd());
    }

    public function testGetCurrentPageOffsetEndOnEndPage(): void
    {
        $this->setAdapterNbResultsAny(90);
        $this->pagerfanta->setMaxPerPage(20);
        $this->pagerfanta->setCurrentPage(5);

        $this->assertSame(90, $this->pagerfanta->getCurrentPageOffsetEnd());
    }

    public function testHaveToPaginateReturnsTrueWhenTheNumberOfResultsIsGreaterThanTheMaxPerPage(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(99);

        $this->assertTrue($this->pagerfanta->haveToPaginate());
    }

    public function testHaveToPaginateReturnsFalseWhenTheNumberOfResultsIsEqualToMaxPerPage(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(100);

        $this->assertFalse($this->pagerfanta->haveToPaginate());
    }

    public function testHaveToPaginateReturnsFalseWhenTheNumberOfResultsIsLessThanMaxPerPage(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(101);

        $this->assertFalse($this->pagerfanta->haveToPaginate());
    }

    public function testHasPreviousPageShouldReturnTrueWhenTheCurrentPageIsGreaterThan1(): void
    {
        $this->setAdapterNbResultsAny(100);

        foreach ([2, 3] as $page) {
            $this->pagerfanta->setCurrentPage($page);
            $this->assertTrue($this->pagerfanta->hasPreviousPage());
        }
    }

    public function testHasPreviousPageShouldReturnFalseWhenTheCurrentPageIs1(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setCurrentPage(1);

        $this->assertFalse($this->pagerfanta->hasPreviousPage());
    }

    public function testGetPreviousPageShouldReturnThePreviousPage(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);

        foreach ([2 => 1, 3 => 2] as $currentPage => $previousPage) {
            $this->pagerfanta->setCurrentPage($currentPage);
            $this->assertSame($previousPage, $this->pagerfanta->getPreviousPage());
        }
    }

    public function testGetPreviousPageShouldThrowALogicExceptionIfThereIsNoPreviousPage(): void
    {
        $this->expectException(\Pagerfanta\Exception\LogicException::class);

        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(1);

        $this->pagerfanta->getPreviousPage();
    }

    public function testHasNextPageShouldReturnTrueIfTheCurrentPageIsNotTheLast(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);

        foreach ([1, 2] as $page) {
            $this->pagerfanta->setCurrentPage($page);
            $this->assertTrue($this->pagerfanta->hasNextPage());
        }
    }

    public function testHasNextPageShouldReturnFalseIfTheCurrentPageIsTheLast(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(10);

        $this->assertFalse($this->pagerfanta->hasNextPage());
    }

    public function testGetNextPageShouldReturnTheNextPage(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);

        foreach ([2 => 3, 3 => 4] as $currentPage => $nextPage) {
            $this->pagerfanta->setCurrentPage($currentPage);
            $this->assertSame($nextPage, $this->pagerfanta->getNextPage());
        }
    }

    public function testGetNextPageShouldThrowALogicExceptionIfTheCurrentPageIsTheLast(): void
    {
        $this->expectException(\Pagerfanta\Exception\LogicException::class);

        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);
        $this->pagerfanta->setCurrentPage(10);

        $this->pagerfanta->getNextPage();
    }

    public function testCountShouldReturnNbResults(): void
    {
        $this->setAdapterNbResultsAny(30);

        $this->assertSame(30, $this->pagerfanta->count());
    }

    public function testPagerfantaShouldImplementCountableInterface(): void
    {
        $this->assertInstanceOf('Countable', $this->pagerfanta);
    }

    public function testGetIteratorShouldReturnCurrentPageResultsIfItIsAnIterator(): void
    {
        $currentPageResults = new \ArrayIterator(['foo']);
        $this->setAdapterGetSlice($currentPageResults);

        $expected = $currentPageResults;
        $this->assertSame($expected, $this->pagerfanta->getIterator());
    }

    public function testGetIteratorShouldReturnTheIteratorOfCurrentPageResultsIfItIsAnIteratorAggregate(): void
    {
        $currentPageResults = new IteratorAggregate();
        $this->setAdapterGetSlice($currentPageResults);

        $expected = $currentPageResults->getIterator();
        $this->assertSame($expected, $this->pagerfanta->getIterator());
    }

    public function testGetIteratorShouldReturnAnArrayIteratorIfCurrentPageResultsIsAnArray(): void
    {
        $currentPageResults = ['foo', 'bar'];
        $this->setAdapterGetSlice($currentPageResults);

        $expected = new \ArrayIterator($currentPageResults);
        $this->assertEquals($expected, $this->pagerfanta->getIterator());
    }

    public function testJsonSerializeShouldReturnAnArrayOfCurrentPageResultsIfItIsAnIterator(): void
    {
        $currentPageResults = new \ArrayIterator(['foo']);
        $this->setAdapterGetSlice($currentPageResults);

        $expected = ['foo'];
        $this->assertSame($expected, $this->pagerfanta->jsonSerialize());
    }

    public function testJsonSerializeShouldReturnAnArrayOfCurrentPageResultsIfItIsAnIteratorAggregate(): void
    {
        $currentPageResults = new IteratorAggregate();
        $this->setAdapterGetSlice($currentPageResults);

        $expected = iterator_to_array($currentPageResults);
        $this->assertSame($expected, $this->pagerfanta->jsonSerialize());
    }

    public function testJsonSerializeShouldReturnAnArrayOfCurrentPageResultsIfCurrentPageResultsIsAnArray(): void
    {
        $currentPageResults = ['foo', 'bar'];
        $this->setAdapterGetSlice($currentPageResults);

        $expected = $currentPageResults;
        $this->assertSame($expected, $this->pagerfanta->jsonSerialize());
    }

    public function testJsonSerializeIsUsedOnJsonEncode(): void
    {
        $currentPageResults = ['foo', 'bar'];
        $this->setAdapterGetSlice($currentPageResults);

        $expected = json_encode($currentPageResults);
        $this->assertSame($expected, json_encode($this->pagerfanta));
    }

    private function setAdapterGetSlice($currentPageResults): void
    {
        $this->adapter
            ->expects($this->any())
            ->method('getSlice')
            ->willReturn($currentPageResults);
    }

    public function testPagerfantaShouldImplementIteratorAggregateInterface(): void
    {
        $this->assertInstanceOf('IteratorAggregate', $this->pagerfanta);
    }

    private function assertResetCurrentPageResults($callback): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);

        $currentPageResults0 = new \ArrayObject();
        $currentPageResults1 = new \ArrayObject();

        $this->adapter
            ->expects($this->at(0))
            ->method('getSlice')
            ->willReturn($currentPageResults0);
        $this->adapter
            ->expects($this->at(1))
            ->method('getSlice')
            ->willReturn($currentPageResults1);

        $this->assertSame($currentPageResults0, $this->pagerfanta->getCurrentPageResults());
        $callback();
        $this->assertSame($currentPageResults1, $this->pagerfanta->getCurrentPageResults());
    }

    public function testGetPageNumberForItemShouldReturnTheGoodPage(): void
    {
        $this->setAdapterNbResultsAny(100);
        $this->pagerfanta->setMaxPerPage(10);

        $this->assertEquals(4, $this->pagerfanta->getPageNumberForItemAtPosition(35));
    }

    public function testGetPageNumberForItemShouldThrowANotIntegerItemExceptionIfTheItemIsNotAnInteger(): void
    {
        $this->expectException(\Pagerfanta\Exception\NotIntegerException::class);

        $this->setAdapterNbResultsAny(100);

        $this->pagerfanta->getPageNumberForItemAtPosition('foo');
    }

    public function testGetPageNumberForItemShouldThrowALogicExceptionIfTheItemIsMoreThanNbPage(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $this->setAdapterNbResultsAny(100);

        $this->pagerfanta->getPageNumberForItemAtPosition(101);
    }
}
