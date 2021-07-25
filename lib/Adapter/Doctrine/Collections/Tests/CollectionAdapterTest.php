<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections\Tests;

use Doctrine\Common\Collections\Collection;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CollectionAdapterTest extends TestCase
{
    /**
     * @var MockObject&Collection<array-key, mixed>
     */
    private $collection;

    /**
     * @var CollectionAdapter<array-key, mixed>
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);

        $this->adapter = new CollectionAdapter($this->collection);
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

        $this->collection->expects($this->once())
            ->method('slice')
            ->willReturn($results);

        $this->assertSame($results, $this->adapter->getSlice(1, 1));
    }

    public function testGetResultsShouldPassTheOffsetAndLengthToTheCollectionSlice(): void
    {
        $this->collection->expects($this->once())
            ->method('slice')
            ->with(5, 12)
            ->willReturn([]);

        $this->adapter->getSlice(5, 12);
    }
}
