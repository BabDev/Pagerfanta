<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;
use PHPUnit\Framework\TestCase;

final class CollectionAdapterTest extends TestCase
{
    /**
     * @var ArrayCollection<array-key, int>
     */
    private ArrayCollection $collection;

    /**
     * @var CollectionAdapter<array-key, int>
     */
    private CollectionAdapter $adapter;

    protected function setUp(): void
    {
        $this->collection = new ArrayCollection(range(1, 150));

        $this->adapter = new CollectionAdapter($this->collection);
    }

    public function testGetNbResultsShouldResultTheCollectionCount(): void
    {
        self::assertSame($this->collection->count(), $this->adapter->getNbResults());
    }

    public function testGetResultsShouldReturnTheCollectionSliceReturnValue(): void
    {
        self::assertSame(array_values(range(6, 17)), array_values($this->adapter->getSlice(5, 12)));
    }
}
