<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Exception\NotValidResultCountException;
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

        self::assertSame($expected, $adapter->getNbResults());
    }

    public function testAdapterRaisesAnErrorIfTheNumberOfResultsCallableReturnsANegativeNumber(): void
    {
        $this->expectException(NotValidResultCountException::class);

        $adapter = new CallbackAdapter(
            static fn () => -10,
            static fn (int $offset, int $length) => []
        );

        $adapter->getNbResults();
    }

    public function testGetSliceShouldReturnTheResultFromTheCallback(): void
    {
        $expected = new \ArrayObject();

        $adapter = new CallbackAdapter(
            static fn () => 0,
            static fn (int $offset, int $length) => $expected
        );

        self::assertSame($expected, $adapter->getSlice(1, 1));
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
