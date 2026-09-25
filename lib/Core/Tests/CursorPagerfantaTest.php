<?php declare(strict_types=1);

namespace Pagerfanta\Tests;

use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CursorPagerfantaTest extends TestCase
{
    /**
     * @var MockObject&CursorAdapterInterface<int>
     */
    private MockObject&CursorAdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(CursorAdapterInterface::class);
    }

    /**
     * @return \Generator<string, array{0: int}>
     */
    public static function dataLessThan1(): \Generator
    {
        yield 'zero' => [0];
        yield 'negative number' => [-1];
    }

    #[DataProvider('dataLessThan1')]
    public function testTheMaxPerPageMustBeAtLeast1(int $maxPerPage): void
    {
        $this->expectException(LessThan1MaxPerPageException::class);

        // @phpstan-ignore-next-line argument.type
        new CursorPagerfanta($this->adapter, $maxPerPage);
    }

    public function testTheSliceIsNotFetchedUntilNeeded(): void
    {
        $this->adapter->expects($this->never())
            ->method('getSlice');

        $pager = new CursorPagerfanta($this->adapter, 5);

        $this->assertSame($this->adapter, $pager->getAdapter());
        $this->assertSame(5, $pager->getMaxPerPage());
        $this->assertNull($pager->getCurrentPosition());
    }

    public function testTheFirstPageIsFetchedWithoutACursor(): void
    {
        $next = new Cursor(['id' => 3]);

        $this->adapter->expects($this->once())
            ->method('getSlice')
            ->with(null, 3)
            ->willReturn(new CursorSlice([1, 2, 3], null, $next));

        $this->adapter->method('supportsBackwardNavigation')
            ->willReturn(true);

        $pager = new CursorPagerfanta($this->adapter, 3);

        $this->assertSame([1, 2, 3], $pager->getCurrentPageResults());
        $this->assertSame([1, 2, 3], iterator_to_array($pager));
        $this->assertSame([1, 2, 3], $pager->jsonSerialize());
        $this->assertCount(3, $pager);
        $this->assertTrue($pager->haveToPaginate());
        $this->assertFalse($pager->hasPreviousPage());
        $this->assertTrue($pager->hasNextPage());
        $this->assertEquals(new CursorPosition($next), $pager->getNextPosition());
    }

    public function testAMiddlePageIsFetchedWithTheCurrentCursor(): void
    {
        $current = new Cursor(['id' => 3]);
        $previous = new Cursor(['id' => 4], Direction::Previous);
        $next = new Cursor(['id' => 6]);

        $this->adapter->expects($this->once())
            ->method('getSlice')
            ->with($current, 3)
            ->willReturn(new CursorSlice([4, 5, 6], $previous, $next));

        $this->adapter->method('supportsBackwardNavigation')
            ->willReturn(true);

        $pager = new CursorPagerfanta($this->adapter, 3, new CursorPosition($current));

        $this->assertSame($current, $pager->getCurrentPosition()?->cursor);
        $this->assertTrue($pager->supportsBackwardNavigation());
        $this->assertTrue($pager->hasPreviousPage());
        $this->assertTrue($pager->hasNextPage());
        $this->assertSame($previous, $pager->getPreviousPosition()->cursor);
        $this->assertSame($next, $pager->getNextPosition()->cursor);
    }

    public function testTheLastPageHasNoNextPage(): void
    {
        $this->adapter->method('getSlice')
            ->willReturn(new CursorSlice([7], new Cursor(['id' => 7], Direction::Previous)));

        $this->adapter->method('supportsBackwardNavigation')
            ->willReturn(true);

        $pager = new CursorPagerfanta($this->adapter, 3, new CursorPosition(new Cursor(['id' => 6])));

        $this->assertTrue($pager->haveToPaginate());
        $this->assertTrue($pager->hasPreviousPage());
        $this->assertFalse($pager->hasNextPage());

        $this->expectException(LogicException::class);

        $pager->getNextPosition();
    }

    public function testAnEmptyResultSetHasNoPages(): void
    {
        $this->adapter->method('getSlice')
            ->willReturn(new CursorSlice([]));

        $this->adapter->method('supportsBackwardNavigation')
            ->willReturn(true);

        $pager = new CursorPagerfanta($this->adapter);

        $this->assertSame([], $pager->getCurrentPageResults());
        $this->assertCount(0, $pager);
        $this->assertFalse($pager->haveToPaginate());
        $this->assertFalse($pager->hasPreviousPage());
        $this->assertFalse($pager->hasNextPage());
    }

    public function testAForwardOnlyAdapterNeverReportsAPreviousPage(): void
    {
        $this->adapter->method('getSlice')
            ->willReturn(new CursorSlice([4, 5, 6], new Cursor(['id' => 4], Direction::Previous), new Cursor(['id' => 6])));

        $this->adapter->method('supportsBackwardNavigation')
            ->willReturn(false);

        $pager = new CursorPagerfanta($this->adapter, 3, new CursorPosition(new Cursor(['id' => 3])));

        $this->assertFalse($pager->supportsBackwardNavigation());
        $this->assertFalse($pager->hasPreviousPage());

        $this->expectException(LogicException::class);

        $pager->getPreviousPosition();
    }

    public function testTheFirstPageHasNoPreviousPosition(): void
    {
        $this->adapter->method('getSlice')
            ->willReturn(new CursorSlice([1, 2, 3], null, new Cursor(['id' => 3])));

        $this->adapter->method('supportsBackwardNavigation')
            ->willReturn(true);

        $this->expectException(LogicException::class);

        (new CursorPagerfanta($this->adapter, 3))->getPreviousPosition();
    }

    public function testAnAdapterReturningMoreItemsThanTheLimitIsRejected(): void
    {
        $this->adapter->method('getSlice')
            ->willReturn(new CursorSlice([1, 2, 3, 4], null, new Cursor(['id' => 3])));

        $this->expectException(LogicException::class);

        (new CursorPagerfanta($this->adapter, 3))->getCurrentPageResults();
    }

    public function testNavigatingReturnsANewPagerWithTheSameConfiguration(): void
    {
        $next = new Cursor(['id' => 3]);

        $this->adapter->expects($this->exactly(2))
            ->method('getSlice')
            ->willReturnCallback(static fn (?Cursor $cursor, int $limit): CursorSlice => $cursor instanceof Cursor ? new CursorSlice([4]) : new CursorSlice([1, 2, 3], null, $next));

        $pager = new CursorPagerfanta($this->adapter, 3);
        $nextPager = $pager->withPosition($pager->getNextPosition());

        $this->assertNotSame($pager, $nextPager);
        $this->assertNull($pager->getCurrentPosition(), 'The original pager is unchanged');
        $this->assertSame([1, 2, 3], $pager->getCurrentPageResults(), 'The original pager keeps its results');
        $this->assertSame($next, $nextPager->getCurrentPosition()?->cursor);
        $this->assertSame(3, $nextPager->getMaxPerPage());
        $this->assertSame([4], $nextPager->getCurrentPageResults());
    }

    public function testTheAutoPagingIteratorWalksEveryPageFromTheCurrentPosition(): void
    {
        $this->adapter->method('getSlice')
            ->willReturnCallback(static fn (?Cursor $cursor, int $limit): CursorSlice => match ($cursor?->fields['id']) {
                null => new CursorSlice([1, 2], null, new Cursor(['id' => 2])),
                2 => new CursorSlice([3, 4], null, new Cursor(['id' => 4])),
                4 => new CursorSlice([5]),
                default => throw new \UnexpectedValueException('Unexpected cursor'),
            });

        $pager = new CursorPagerfanta($this->adapter, 2);

        $this->assertSame([1, 2, 3, 4, 5], iterator_to_array($pager->autoPagingIterator(), false));
        $this->assertNull($pager->getCurrentPosition(), 'The pager is not changed by auto paging');
    }
}
