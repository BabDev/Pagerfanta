<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Adapter\ConcatenationAdapter;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConcatenationAdapterTest extends TestCase
{
    public function testConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ConcatenationAdapter([
            new ArrayAdapter([]),
            new NullAdapter(),
            new FixedAdapter(0, []),
        ]);

        new ConcatenationAdapter([
            new ArrayAdapter([]),
            'foo',
        ]);
    }

    public function testGetNbResults(): void
    {
        $adapter = new ConcatenationAdapter([
            new ArrayAdapter(['foo', 'bar', 'baz']),
        ]);
        $this->assertEquals(3, $adapter->getNbResults());

        $adapter = new ConcatenationAdapter([
            new ArrayAdapter(array_fill(0, 4, 'foo')),
            new ArrayAdapter(array_fill(0, 6, 'bar')),
            new ArrayAdapter(['baq']),
        ]);
        $this->assertEquals(11, $adapter->getNbResults());

        $adapter = new ConcatenationAdapter([]);
        $this->assertEquals(0, $adapter->getNbResults());
    }

    public function testGetResults(): void
    {
        $adapter = new ConcatenationAdapter([
            new ArrayAdapter([1, 2, 3, 4, 5, 6]),
            new ArrayAdapter([7, 8, 9, 10, 11, 12, 13, 14]),
            new ArrayAdapter([15, 16, 17]),
        ]);
        $this->assertEquals([8, 9, 10], $adapter->getSlice(7, 3));
        $this->assertEquals([5, 6, 7, 8], $adapter->getSlice(4, 4));
        $this->assertEquals([6, 7, 8, 9, 10, 11, 12, 13, 14, 15], $adapter->getSlice(5, 10));
        $this->assertEquals([16, 17], $adapter->getSlice(15, 5));
    }

    public function testWithTraversableAdapter(): void
    {
        $adapter = new ConcatenationAdapter([
            new CallbackAdapter(
                function () {
                    return 5;
                },
                function ($offset, $length) {
                    return new \ArrayIterator(\array_slice([1, 2, 3, 4, 5], $offset, $length));
                }
            ),
            new CallbackAdapter(
                function () {
                    return 3;
                },
                function ($offset, $length) {
                    return new \ArrayIterator(\array_slice([6, 7, 8], $offset, $length));
                }
            ),
        ]);
        $this->assertEquals([2, 3], $adapter->getSlice(1, 2));
        $this->assertEquals([4, 5, 6], $adapter->getSlice(3, 3));
    }
}
