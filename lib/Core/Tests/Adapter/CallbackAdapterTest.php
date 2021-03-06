<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\CallbackAdapter;
use PHPUnit\Framework\TestCase;

final class CallbackAdapterTest extends TestCase
{
    public function testAdapterReturnsNumberOfItemsInResultSet(): void
    {
        $expected = 42;

        $adapter = new CallbackAdapter(
            static fn () => $expected,
            static fn (int $offset, int $length) => []
        );

        $this->assertSame($expected, $adapter->getNbResults());
    }

    public function testGetSliceShouldReturnTheResultFromTheCallback(): void
    {
        $expected = new \ArrayObject();

        $adapter = new CallbackAdapter(
            static fn () => 0,
            static fn (int $offset, int $length) => $expected
        );

        $this->assertSame($expected, $adapter->getSlice(1, 1));
    }

    public function testGetSliceShouldPassTheOffsetAndLengthToTheGetSliceCallback(): void
    {
        $sliceCallable = function (int $offset, int $length): iterable {
            $this->assertSame(10, $offset);
            $this->assertSame(18, $length);

            return [];
        };

        $adapter = new CallbackAdapter(
            static fn () => 10,
            $sliceCallable
        );

        $adapter->getSlice(10, 18);
    }
}
