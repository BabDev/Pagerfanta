<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\PropelAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * PropelAdapterTest.
 *
 * @author William DURAND <william.durand1@gmail.com>
 */
class PropelAdapterTest extends TestCase
{
    /**
     * @var MockObject|\ModelCriteria
     */
    private $query;

    /**
     * @var PropelAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->query = $this->createMock(\ModelCriteria::class);
        $this->adapter = new PropelAdapter($this->query);
    }

    public function testGetQuery(): void
    {
        $this->assertSame($this->query, $this->adapter->getQuery());
    }

    public function testGetNbResults(): void
    {
        $this->query->expects($this->once())
            ->method('limit')
            ->with(0);

        $this->query->expects($this->once())
            ->method('offset')
            ->with(0);

        $this->query->expects($this->once())
            ->method('count')
            ->willReturn(100);

        $this->assertSame(100, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 14;
        $length = 20;
        $slice = new \ArrayObject();

        $this->query->expects($this->once())
            ->method('limit')
            ->with($length);

        $this->query->expects($this->once())
            ->method('offset')
            ->with($offset);

        $this->query->expects($this->once())
            ->method('find')
            ->willReturn($slice);

        $this->assertSame($slice, $this->adapter->getSlice($offset, $length));
    }
}
