<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Position;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\TestCase;

final class CursorPositionTest extends TestCase
{
    public function testTheCursorIsExposed(): void
    {
        $cursor = new Cursor(['p.id' => 10]);

        $this->assertSame($cursor, (new CursorPosition($cursor))->cursor);
    }
}
