<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Adapter\ConcatenationAdapter;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

final class ConcatenationAdapterTest extends TestCase
{
    #[DoesNotPerformAssertions]
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

        self::assertSame(3, $adapter->getNbResults());
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
        self::assertSame(11, $adapter->getNbResults());
    }

    public function testGetNbResultsWithNoAdapters(): void
    {
        $adapter = new ConcatenationAdapter([]);
        self::assertSame(0, $adapter->getNbResults());
    }

    public function testGetResults(): void
    {
        $adapter = new ConcatenationAdapter([
            new ArrayAdapter([1, 2, 3, 4, 5, 6]),
            new ArrayAdapter([7, 8, 9, 10, 11, 12, 13, 14]),
            new ArrayAdapter([15, 16, 17]),
        ]);
        self::assertSame([8, 9, 10], $adapter->getSlice(7, 3));
        self::assertSame([5, 6, 7, 8], $adapter->getSlice(4, 4));
        self::assertSame([6, 7, 8, 9, 10, 11, 12, 13, 14, 15], $adapter->getSlice(5, 10));
        self::assertSame([16, 17], $adapter->getSlice(15, 5));
    }

    public function testGetResultsWithTraversableAdapter(): void
    {
        $adapter = new ConcatenationAdapter(
            [
                new CallbackAdapter(
                    static fn () => 5,
                    static fn (int $offset, int $length) => new \ArrayIterator(\array_slice([1, 2, 3, 4, 5], $offset, $length))
                ),
                new CallbackAdapter(
                    static fn () => 3,
                    static fn (int $offset, int $length) => new \ArrayIterator(\array_slice([6, 7, 8], $offset, $length))
                ),
            ]
        );

        self::assertSame([2, 3], $adapter->getSlice(1, 2));
        self::assertSame([4, 5, 6], $adapter->getSlice(3, 3));
    }
}
