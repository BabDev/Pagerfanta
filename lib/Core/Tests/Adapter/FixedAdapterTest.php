<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\FixedAdapter;
use PHPUnit\Framework\TestCase;

final class FixedAdapterTest extends TestCase
{
    public function testGetNbResults(): void
    {
        $adapter = new FixedAdapter(5, []);

        $this->assertSame(5, $adapter->getNbResults());
    }

    public function dataGetSlice(): \Generator
    {
        yield 'from array' => [['a', 'b']];
        yield 'from iterable object' => [new \ArrayObject()];
    }

    /**
     * @dataProvider dataGetSlice
     */
    public function testGetSlice(iterable $results): void
    {
        $adapter = new FixedAdapter(5, $results);

        $this->assertSame($results, $adapter->getSlice(0, 10));
        $this->assertSame($results, $adapter->getSlice(10, 20));
    }
}
