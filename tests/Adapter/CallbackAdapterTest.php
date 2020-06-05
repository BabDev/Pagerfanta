<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CallbackAdapterTest extends TestCase
{
    /**
     * @dataProvider notCallbackProvider
     */
    public function testConstructorShouldThrowAnInvalidArgumentExceptionIfTheGetNbResultsCallbackIsNotACallback($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CallbackAdapter($value, function (): void {});
    }

    /**
     * @dataProvider notCallbackProvider
     */
    public function testConstructorShouldThrowAnInvalidArgumentExceptionIfTheGetSliceCallbackIsNotACallback($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CallbackAdapter(function (): void {}, $value);
    }

    public function notCallbackProvider()
    {
        return [
            ['foo'],
            [1],
        ];
    }

    public function testGetNbResultShouldReturnTheGetNbResultsCallbackReturnValue(): void
    {
        $getNbResultsCallback = function () {
            return 42;
        };
        $adapter = new CallbackAdapter($getNbResultsCallback, function (): void {});

        $this->assertEquals(42, $adapter->getNbResults());
    }

    public function testGetSliceShouldReturnTheGetSliceCallbackReturnValue(): void
    {
        $results = new \ArrayObject();
        $getSliceCallback = function () use ($results) {
            return $results;
        };

        $adapter = new CallbackAdapter(function (): void {}, $getSliceCallback);

        $this->assertSame($results, $adapter->getSlice(1, 1));
    }

    public function testGetSliceShouldPassTheOffsetAndLengthToTheGetSliceCallback(): void
    {
        $testCase = $this;
        $getSliceCallback = function ($offset, $length) use ($testCase): void {
            $testCase->assertSame(10, $offset);
            $testCase->assertSame(18, $length);
        };

        $adapter = new CallbackAdapter(function (): void {}, $getSliceCallback);
        $adapter->getSlice(10, 18);
    }
}
