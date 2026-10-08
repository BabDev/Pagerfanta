<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CursorSliceTest extends TestCase
{
    public function testTheSliceDefaultsToHavingNoNeighbouringPages(): void
    {
        $slice = new CursorSlice([]);

        $this->assertSame([], $slice->items);
        $this->assertNull($slice->previous);
        $this->assertNull($slice->next);
    }

    public function testTheSliceExposesItsItemsAndCursors(): void
    {
        $previous = new Cursor(['id' => 1], Direction::Previous);
        $next = new Cursor(['id' => 2], Direction::Next);

        $slice = new CursorSlice([1, 2], $previous, $next);

        $this->assertSame([1, 2], $slice->items);
        $this->assertSame($previous, $slice->previous);
        $this->assertSame($next, $slice->next);
    }

    public function testThePreviousCursorMustHaveThePreviousDirection(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CursorSlice([1], new Cursor(['id' => 1], Direction::Next));
    }

    public function testTheNextCursorMustHaveTheNextDirection(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CursorSlice([1], null, new Cursor(['id' => 1], Direction::Previous));
    }

    /**
     * @return \Closure(int, Direction): Cursor
     */
    private function cursorFactory(): \Closure
    {
        return static fn (int $item, Direction $direction): Cursor => new Cursor(['id' => $item], $direction);
    }

    public function testALookaheadSliceForTheFirstPageWithMoreItems(): void
    {
        $slice = CursorSlice::fromLookahead([1, 2, 3, 4], 3, null, $this->cursorFactory());

        $this->assertSame([1, 2, 3], $slice->items);
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['id' => 3]), $slice->next);
    }

    public function testALookaheadSliceForTheOnlyPage(): void
    {
        $this->assertEquals(new CursorSlice([1, 2, 3]), CursorSlice::fromLookahead([1, 2, 3], 3, null, $this->cursorFactory()));
    }

    public function testALookaheadSliceAfterANextCursor(): void
    {
        $slice = CursorSlice::fromLookahead([4, 5], 3, new Cursor(['id' => 3]), $this->cursorFactory());

        $this->assertSame([4, 5], $slice->items);
        $this->assertEquals(new Cursor(['id' => 4], Direction::Previous), $slice->previous, 'Arriving from a next cursor implies a previous page');
        $this->assertNull($slice->next);
    }

    public function testALookaheadSliceBeforeAPreviousCursorIsReversed(): void
    {
        $slice = CursorSlice::fromLookahead([6, 5, 4, 3], 3, new Cursor(['id' => 7], Direction::Previous), $this->cursorFactory());

        $this->assertSame([4, 5, 6], $slice->items);
        $this->assertEquals(new Cursor(['id' => 4], Direction::Previous), $slice->previous);
        $this->assertEquals(new Cursor(['id' => 6]), $slice->next, 'Arriving from a previous cursor implies a next page');
    }

    public function testALookaheadSliceAtTheStartOfTheList(): void
    {
        $slice = CursorSlice::fromLookahead([2, 1], 3, new Cursor(['id' => 3], Direction::Previous), $this->cursorFactory());

        $this->assertSame([1, 2], $slice->items);
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['id' => 2]), $slice->next);
    }

    public function testAnEmptyLookaheadSliceHasNoCursors(): void
    {
        $this->assertEquals(new CursorSlice([]), CursorSlice::fromLookahead([], 3, new Cursor(['id' => 3]), $this->cursorFactory()));
    }
}
