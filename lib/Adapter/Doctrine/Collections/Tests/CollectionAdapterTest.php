<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;
use PHPUnit\Framework\TestCase;

final class CollectionAdapterTest extends TestCase
{
    /**
     * @var ArrayCollection<int, int<1, 150>>
     */
    private ArrayCollection $collection;

    /**
     * @var CollectionAdapter<int, int<1, 150>>
     */
    private CollectionAdapter $adapter;

    protected function setUp(): void
    {
        $this->collection = new ArrayCollection(range(1, 150));

        $this->adapter = new CollectionAdapter($this->collection);
    }

    public function testGetNbResultsShouldResultTheCollectionCount(): void
    {
        $this->assertSame($this->collection->count(), $this->adapter->getNbResults());
    }

    public function testGetResultsShouldReturnTheCollectionSliceReturnValue(): void
    {
        $slice = $this->adapter->getSlice(5, 12);

        \assert(\is_array($slice));

        $this->assertSame(array_values(range(6, 17)), array_values($slice));
    }
}
