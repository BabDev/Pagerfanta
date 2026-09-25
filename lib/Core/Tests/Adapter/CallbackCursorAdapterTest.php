<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\TestCase;

final class CallbackCursorAdapterTest extends TestCase
{
    public function testTheCallableReceivesTheCursorAndLimit(): void
    {
        $cursor = new Cursor(['id' => 3]);
        $slice = new CursorSlice([4, 5]);

        $adapter = new CallbackCursorAdapter(function (?Cursor $givenCursor, int $limit) use ($cursor, $slice): CursorSlice {
            $this->assertSame($cursor, $givenCursor);
            $this->assertSame(2, $limit);

            return $slice;
        });

        $this->assertSame($slice, $adapter->getSlice($cursor, 2));
    }

    public function testBackwardNavigationIsNotSupportedByDefault(): void
    {
        $this->assertFalse((new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([])))->supportsBackwardNavigation());
    }

    public function testBackwardNavigationSupportCanBeEnabled(): void
    {
        $this->assertTrue((new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([]), true))->supportsBackwardNavigation());
    }

    public function testAForwardOnlyAdapterHasNoPreviousPageInThePager(): void
    {
        $adapter = new CallbackCursorAdapter(static fn (?Cursor $cursor, int $limit): CursorSlice => new CursorSlice([4, 5], new Cursor(['id' => 4], Direction::Previous), new Cursor(['id' => 5])));

        $pager = new CursorPagerfanta($adapter, 2, new CursorPosition(new Cursor(['id' => 3])));

        $this->assertFalse($pager->supportsBackwardNavigation());
        $this->assertFalse($pager->hasPreviousPage());
        $this->assertTrue($pager->hasNextPage());
    }

    public function testTheCallableMustReturnACursorSlice(): void
    {
        $this->expectException(LogicException::class);

        // @phpstan-ignore-next-line argument.type
        (new CallbackCursorAdapter(static fn (): array => [1, 2]))->getSlice(null, 2);
    }
}
