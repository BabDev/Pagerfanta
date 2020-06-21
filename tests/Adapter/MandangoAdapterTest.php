<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Mandango\Query;
use Pagerfanta\Adapter\MandangoAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MandangoAdapterTest extends TestCase
{
    /**
     * @var MockObject|Query
     */
    private $query;

    /**
     * @var MandangoAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->query = $this->createMock(Query::class);

        $this->adapter = new MandangoAdapter($this->query);
    }

    public function testGetQuery(): void
    {
        $this->assertSame($this->query, $this->adapter->getQuery());
    }

    public function testGetNbResults(): void
    {
        $this->query->expects($this->once())
            ->method('count')
            ->willReturn(100);

        $this->assertSame(100, $this->adapter->getNbResults());
    }

    public function testGetResults(): void
    {
        $offset = 14;
        $length = 30;
        $slice = new \ArrayObject();

        $this->prepareQuerySkip($offset);
        $this->prepareQueryLimit($length);
        $this->prepareQueryAll($slice);

        $this->assertSame($slice, $this->adapter->getSlice($offset, $length));
    }

    private function prepareQueryLimit(int $limit): void
    {
        $this->query->expects($this->once())
            ->method('limit')
            ->with($limit)
            ->willReturn($this->query);
    }

    private function prepareQuerySkip(int $skip): void
    {
        $this->query->expects($this->once())
            ->method('skip')
            ->with($skip)
            ->willReturn($this->query);
    }

    private function prepareQueryAll(object $all): void
    {
        $this->query->expects($this->once())
            ->method('all')
            ->willReturn($all);
    }
}
