<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\MongoAdapter;
use PHPUnit\Framework\TestCase;

class MongoAdapterTest extends TestCase
{
    protected $cursor;

    /**
     * @var MongoAdapter
     */
    protected $adapter;

    protected function setUp(): void
    {
        if ($this->isMongoNotAvailable()) {
            $this->markTestSkipped('Mongo is not available.');
        }

        $this->cursor = $this->createCursorMock();
        $this->adapter = new MongoAdapter($this->cursor);
    }

    private function isMongoNotAvailable()
    {
        return !\extension_loaded('mongo');
    }

    private function createCursorMock()
    {
        return $this
            ->getMockBuilder('\MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetCursor(): void
    {
        $this->assertSame($this->cursor, $this->adapter->getCursor());
    }

    public function testGetNbResultsShouldReturnTheCursorCount(): void
    {
        $this->cursor
            ->expects($this->once())
            ->method('count')
            ->willReturn(100);

        $this->assertSame(100, $this->adapter->getNbResults());
    }

    public function testGetSliceShouldPassTheOffsetAndLengthToTheCursor(): void
    {
        $offset = 12;
        $length = 16;

        $this->cursor
            ->expects($this->once())
            ->method('limit')
            ->with($length);
        $this->cursor
            ->expects($this->once())
            ->method('skip')
            ->with($offset);

        $this->adapter->getSlice($offset, $length);
    }

    public function testGetSliceShouldReturnTheCursor(): void
    {
        $this->cursor
            ->expects($this->any())
            ->method('limit');
        $this->cursor
            ->expects($this->any())
            ->method('skip');

        $this->assertSame($this->cursor, $this->adapter->getSlice(1, 1));
    }
}
