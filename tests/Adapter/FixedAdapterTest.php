<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\FixedAdapter;
use PHPUnit\Framework\TestCase;

class FixedAdapterTest extends TestCase
{
    public function testGetNbResults(): void
    {
        $adapter = new FixedAdapter(5, []);
        $this->assertSame(5, $adapter->getNbResults());
    }

    /**
     * @dataProvider getSliceProvider
     */
    public function testGetSlice($results): void
    {
        $adapter = new FixedAdapter(5, $results);
        $this->assertSame($results, $adapter->getSlice(0, 10));
        $this->assertSame($results, $adapter->getSlice(10, 20));
    }

    public function getSliceProvider()
    {
        return [
            [['a', 'b']],
            [new \ArrayIterator()],
        ];
    }
}
