<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\ArrayCursorAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Adapter\TransformingCursorAdapter;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use PHPUnit\Framework\TestCase;

final class TransformingCursorAdapterTest extends TestCase
{
    public function testTheItemsAreTransformedAndTheCursorsAreKept(): void
    {
        $inner = new ArrayCursorAdapter(range(1, 5), static fn (int $item): array => ['id' => $item]);

        $adapter = new TransformingCursorAdapter($inner, static fn (int $item, int $key): string => \sprintf('%d: item %d', $key, $item));

        $slice = $adapter->getSlice(new Cursor(['id' => 1]), 2);

        $this->assertSame(['0: item 2', '1: item 3'], $slice->items);
        $this->assertEquals(new Cursor(['id' => 2], Direction::Previous), $slice->previous);
        $this->assertEquals(new Cursor(['id' => 3], Direction::Next), $slice->next);
    }

    public function testBackwardNavigationSupportComesFromTheDecoratedAdapter(): void
    {
        $transformer = static fn (mixed $item): mixed => $item;

        $this->assertTrue((new TransformingCursorAdapter(new ArrayCursorAdapter([], static fn (mixed $item): array => ['id' => 1]), $transformer))->supportsBackwardNavigation());
        $this->assertFalse((new TransformingCursorAdapter(new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([])), $transformer))->supportsBackwardNavigation());
    }
}
