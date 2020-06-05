<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Doctrine\Common\Collections\Collection;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoctrineCollectionAdapterTest extends TestCase
{
    /**
     * @var MockObject|Collection
     */
    private $collection;

    /**
     * @var DoctrineCollectionAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);

        $this->adapter = new DoctrineCollectionAdapter($this->collection);
    }

    public function testGetCollectionShouldReturnTheCollection(): void
    {
        $this->assertSame($this->collection, $this->adapter->getCollection());
    }

    public function testGetNbResultsShouldResultTheCollectionCount(): void
    {
        $this->collection
            ->expects($this->once())
            ->method('count')
            ->willReturn(120);

        $this->assertSame(120, $this->adapter->getNbResults());
    }

    public function testGetResultsShouldReturnTheCollectionSliceReturnValue(): void
    {
        $results = new \ArrayObject();
        $this->collection
            ->expects($this->once())
            ->method('slice')
            ->willReturn($results);

        $this->assertSame($results, $this->adapter->getSlice(1, 1));
    }

    public function testGetResultsShouldPassTheOffsetAndLengthToTheCollectionSlice(): void
    {
        $this->collection
            ->expects($this->once())
            ->method('slice')
            ->with(5, 12);

        $this->adapter->getSlice(5, 12);
    }
}
