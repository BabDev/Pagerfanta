<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Exception\NotValidResultCountException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FixedAdapterTest extends TestCase
{
    public function testConstructorRejectsResultCountLessThanZero(): void
    {
        $this->expectException(NotValidResultCountException::class);

        new FixedAdapter(-5, []);
    }

    public function testGetNbResults(): void
    {
        $adapter = new FixedAdapter(5, []);

        $this->assertSame(5, $adapter->getNbResults());
    }

    public static function dataGetSlice(): \Generator
    {
        yield 'from array' => [['a', 'b']];
        yield 'from iterable object' => [new \ArrayObject()];
    }

    /**
     * @param iterable<mixed> $results
     */
    #[DataProvider('dataGetSlice')]
    public function testGetSlice(iterable $results): void
    {
        $adapter = new FixedAdapter(5, $results);

        $this->assertSame($results, $adapter->getSlice(0, 10));
        $this->assertSame($results, $adapter->getSlice(10, 20));
    }
}
