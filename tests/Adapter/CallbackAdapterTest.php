<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CallbackAdapterTest extends TestCase
{
    public function notCallbackProvider(): \Generator
    {
        yield 'string that is not a function' => ['foo'];
        yield 'integer' => [1];
        yield 'array that is not callable' => [['foo', 'bar']];
    }

    /**
     * @dataProvider notCallbackProvider
     */
    public function testTheConstructorRejectsTheNbResultsArgumentIfItIsNotACallable($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CallbackAdapter($value, static function (int $offset, int $length): iterable { return []; });
    }

    /**
     * @dataProvider notCallbackProvider
     */
    public function testTheConstructorRejectsTheSliceArgumentIfItIsNotACallable($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CallbackAdapter(static function (): int { return 0; }, $value);
    }

    public function testAdapterReturnsNumberOfItemsInResultSet(): void
    {
        $expected = 42;

        $adapter = new CallbackAdapter(
            static function () use ($expected): int { return $expected; },
            static function (int $offset, int $length): iterable { return []; }
        );

        $this->assertSame($expected, $adapter->getNbResults());
    }

    public function testGetSliceShouldReturnTheResultFromTheCallback(): void
    {
        $expected = new \ArrayObject();

        $adapter = new CallbackAdapter(
            static function (): int { return 0; },
            static function (int $offset, int $length) use ($expected): iterable { return $expected; }
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
            static function (): int { return 10; },
            $sliceCallable
        );
        $adapter->getSlice(10, 18);
    }
}
