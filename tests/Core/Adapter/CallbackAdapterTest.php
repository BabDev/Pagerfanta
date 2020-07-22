<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Core\Adapter;

use Pagerfanta\Adapter\CallbackAdapter;
use PHPUnit\Framework\TestCase;

final class CallbackAdapterTest extends TestCase
{
    public function testAdapterReturnsNumberOfItemsInResultSet(): void
    {
        $expected = 42;

        $adapter = new CallbackAdapter(
            static function () use ($expected): int { return $expected; },
            static function (int $offset, int $length): void {}
        );

        $this->assertSame($expected, $adapter->getNbResults());
    }

    public function testGetSliceShouldReturnTheResultFromTheCallback(): void
    {
        $expected = new \ArrayObject();

        $adapter = new CallbackAdapter(
            static function (): void {},
            static function (int $offset, int $length) use ($expected): iterable { return $expected; }
        );

        $this->assertSame($expected, $adapter->getSlice(1, 1));
    }

    public function testGetSliceShouldPassTheOffsetAndLengthToTheGetSliceCallback(): void
    {
        $offset = 10;
        $length = 18;

        $sliceCallable = function (int $offset, int $length): iterable {
            $this->assertSame(10, $offset);
            $this->assertSame(18, $length);

            return [];
        };

        $adapter = new CallbackAdapter(
            static function (): void {},
            $sliceCallable
        );

        $adapter->getSlice(10, 18);
    }
}
