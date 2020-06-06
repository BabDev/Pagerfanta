<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\CallbackAdapter;
use PHPUnit\Framework\TestCase;

class CallbackAdapterTest extends TestCase
{
    public function testGetNbResultShouldReturnTheGetNbResultsCallbackReturnValue(): void
    {
        $getNbResultsCallback = function (): int {
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

        $adapter = new CallbackAdapter(function (): int { return 1; }, $getSliceCallback);

        $this->assertSame($results, $adapter->getSlice(1, 1));
    }

    public function testGetSliceShouldPassTheOffsetAndLengthToTheGetSliceCallback(): void
    {
        $getSliceCallback = function (int $offset, int $length): iterable {
            $this->assertSame(10, $offset);
            $this->assertSame(18, $length);

            return new \ArrayIterator();
        };

        $adapter = new CallbackAdapter(function (): int { return 10; }, $getSliceCallback);
        $adapter->getSlice(10, 18);
    }
}
