<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Core\Adapter;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Adapter\ConcatenationAdapter;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ConcatenationAdapterTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testAdapterIsInstantiatedWhenOnlyAdaptersAreProvided(): void
    {
        new ConcatenationAdapter(
            [
                new ArrayAdapter([]),
                new NullAdapter(),
                new FixedAdapter(0, []),
            ]
        );
    }

    public function testAdapterIsNotInstantiatedWhenANonAdapterIsProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The $adapters argument of the %s constructor expects all items to be an instance of %s.', ConcatenationAdapter::class, AdapterInterface::class));

        new ConcatenationAdapter(
            [
                new ArrayAdapter([]),
                'foo',
            ]
        );
    }

    public function testGetNbResultsFromSingleAdapter(): void
    {
        $adapter = new ConcatenationAdapter(
            [
                new ArrayAdapter(['foo', 'bar', 'baz']),
            ]
        );

        $this->assertEquals(3, $adapter->getNbResults());
    }

    public function testGetNbResultsFromMultipleAdapters(): void
    {
        $adapter = new ConcatenationAdapter(
            [
                new ArrayAdapter(array_fill(0, 4, 'foo')),
                new ArrayAdapter(array_fill(0, 6, 'bar')),
                new ArrayAdapter(['baq']),
            ]
        );
        $this->assertEquals(11, $adapter->getNbResults());
    }

    public function testGetNbResultsWithNoAdapters(): void
    {
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

    public function testGetResultsWithTraversableAdapter(): void
    {
        $adapter = new ConcatenationAdapter(
            [
                new CallbackAdapter(
                    static function (): int { return 5; },
                    static function (int $offset, int $length): iterable { return new \ArrayIterator(\array_slice([1, 2, 3, 4, 5], $offset, $length)); }
                ),
                new CallbackAdapter(
                    static function (): int { return 3; },
                    static function (int $offset, int $length): iterable { return new \ArrayIterator(\array_slice([6, 7, 8], $offset, $length)); }
                ),
            ]
        );

        $this->assertEquals([2, 3], $adapter->getSlice(1, 2));
        $this->assertEquals([4, 5, 6], $adapter->getSlice(3, 3));
    }
}
