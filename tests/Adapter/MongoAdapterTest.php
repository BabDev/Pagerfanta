<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\MongoAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @requires extension mongo
 */
class MongoAdapterTest extends TestCase
{
    /**
     * @var MockObject|\MongoCursor
     */
    private $cursor;

    /**
     * @var MongoAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->cursor = $this->createMock(\MongoCursor::class);
        $this->adapter = new MongoAdapter($this->cursor);
    }

    public function testGetCursor(): void
    {
        $this->assertSame($this->cursor, $this->adapter->getCursor());
    }

    public function testGetNbResultsShouldReturnTheCursorCount(): void
    {
        $this->cursor->expects($this->once())
            ->method('count')
            ->willReturn(100);

        $this->assertSame(100, $this->adapter->getNbResults());
    }

    public function testGetSliceShouldPassTheOffsetAndLengthToTheCursor(): void
    {
        $offset = 12;
        $length = 16;

        $this->cursor->expects($this->once())
            ->method('limit')
            ->with($length);

        $this->cursor->expects($this->once())
            ->method('skip')
            ->with($offset);

        $this->assertSame($this->cursor, $this->adapter->getSlice($offset, $length));
    }
}
