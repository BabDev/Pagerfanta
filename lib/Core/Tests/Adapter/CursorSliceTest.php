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
}
