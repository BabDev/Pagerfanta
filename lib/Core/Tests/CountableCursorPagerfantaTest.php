<?php declare(strict_types=1);

namespace Pagerfanta\Tests;

use Pagerfanta\Adapter\CountableAdapterInterface;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CountableCursorPagerfantaTest extends TestCase
{
    /**
     * @var MockObject&CursorAdapterInterface<int>&CountableAdapterInterface
     */
    private MockObject&CursorAdapterInterface&CountableAdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMockForIntersectionOfInterfaces([CursorAdapterInterface::class, CountableAdapterInterface::class]);
    }

    public function testTheMaxPerPageMustBeAtLeast1(): void
    {
        $this->expectException(LessThan1MaxPerPageException::class);

        // @phpstan-ignore-next-line argument.type
        new CountableCursorPagerfanta($this->adapter, 0);
    }

    public function testTheTotalIsFetchedOnceAndOnlyWhenRequested(): void
    {
        $this->adapter->method('getSlice')
            ->willReturn(new CursorSlice([1, 2, 3], null, new Cursor(['id' => 3])));

        $this->adapter->expects($this->once())
            ->method('getNbResults')
            ->willReturn(7);

        $pager = new CountableCursorPagerfanta($this->adapter, 3);

        $this->assertCount(3, $pager, 'count() is the number of items on the page');
        $this->assertSame(7, $pager->getNbResults());
        $this->assertSame(7, $pager->getNbResults());
    }

    public function testThePagerDelegatesToTheCursorPager(): void
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

        $pager = new CountableCursorPagerfanta($this->adapter, 3, new CursorPosition($current));

        $this->assertSame($this->adapter, $pager->getAdapter());
        $this->assertSame(3, $pager->getMaxPerPage());
        $this->assertSame($current, $pager->getCurrentPosition()?->cursor);
        $this->assertSame([4, 5, 6], $pager->getCurrentPageResults());
        $this->assertSame([4, 5, 6], iterator_to_array($pager));
        $this->assertSame([4, 5, 6], $pager->jsonSerialize());
        $this->assertTrue($pager->supportsBackwardNavigation());
        $this->assertTrue($pager->haveToPaginate());
        $this->assertTrue($pager->hasPreviousPage());
        $this->assertTrue($pager->hasNextPage());
        $this->assertSame($previous, $pager->getPreviousPosition()->cursor);
        $this->assertSame($next, $pager->getNextPosition()->cursor);
    }

    public function testThereIsNoNextPositionOnTheLastPage(): void
    {
        $this->adapter->method('getSlice')
            ->willReturn(new CursorSlice([1]));

        $this->expectException(LogicException::class);

        (new CountableCursorPagerfanta($this->adapter))->getNextPosition();
    }

    public function testNavigatingReturnsANewCountablePager(): void
    {
        $next = new Cursor(['id' => 3]);

        $this->adapter->method('getSlice')
            ->willReturnCallback(static fn (?Cursor $cursor, int $limit): CursorSlice => $cursor instanceof Cursor ? new CursorSlice([4]) : new CursorSlice([1, 2, 3], null, $next));

        $pager = new CountableCursorPagerfanta($this->adapter, 3);
        $nextPager = $pager->withPosition($pager->getNextPosition());

        $this->assertNotSame($pager, $nextPager);
        $this->assertNull($pager->getCurrentPosition());
        $this->assertSame($next, $nextPager->getCurrentPosition()?->cursor);
        $this->assertSame(3, $nextPager->getMaxPerPage());
        $this->assertSame([4], $nextPager->getCurrentPageResults());
    }

    public function testTheAutoPagingIteratorWalksEveryPage(): void
    {
        $this->adapter->method('getSlice')
            ->willReturnCallback(static fn (?Cursor $cursor, int $limit): CursorSlice => $cursor instanceof Cursor ? new CursorSlice([3]) : new CursorSlice([1, 2], null, new Cursor(['id' => 2])));

        $this->assertSame([1, 2, 3], iterator_to_array((new CountableCursorPagerfanta($this->adapter, 2))->autoPagingIterator(), false));
    }
}
