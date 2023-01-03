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
    private MockObject&Collection $collection;

    /**
     * @var CollectionAdapter<array-key, mixed>
     */
    private CollectionAdapter $adapter;

    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);

        $this->adapter = new CollectionAdapter($this->collection);
    }

    public function testGetNbResultsShouldResultTheCollectionCount(): void
    {
        $this->collection
            ->expects(self::once())
            ->method('count')
            ->willReturn(120);

        self::assertSame(120, $this->adapter->getNbResults());
    }

    public function testGetResultsShouldReturnTheCollectionSliceReturnValue(): void
    {
        $results = new \ArrayObject();

        $this->collection->expects(self::once())
            ->method('slice')
            ->willReturn($results);

        self::assertSame($results, $this->adapter->getSlice(1, 1));
    }

    public function testGetResultsShouldPassTheOffsetAndLengthToTheCollectionSlice(): void
    {
        $this->collection->expects(self::once())
            ->method('slice')
            ->with(5, 12)
            ->willReturn([]);

        $this->adapter->getSlice(5, 12);
    }
}
