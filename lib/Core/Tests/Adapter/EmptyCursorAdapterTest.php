<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Adapter\EmptyCursorAdapter;
use Pagerfanta\Cursor\Cursor;
use PHPUnit\Framework\TestCase;

final class EmptyCursorAdapterTest extends TestCase
{
    public function testTheAdapterIsAlwaysEmpty(): void
    {
        $adapter = new EmptyCursorAdapter();

        $this->assertSame(0, $adapter->getNbResults());
        $this->assertFalse($adapter->supportsBackwardNavigation());
        $this->assertEquals(new CursorSlice([]), $adapter->getSlice(null, 10));
        $this->assertEquals(new CursorSlice([]), $adapter->getSlice(new Cursor(['id' => 1]), 10));
    }
}
