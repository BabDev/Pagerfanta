<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\ArrayAdapter;
use PHPUnit\Framework\TestCase;

final class ArrayAdapterTest extends TestCase
{
    /**
     * @var int[]
     * @phpstan-var array<int<1, 100>>
     */
    private $array;

    /**
     * @var ArrayAdapter<int>
     * @phpstan-var ArrayAdapter<int<1, 100>>
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->array = range(1, 100);
        $this->adapter = new ArrayAdapter($this->array);
    }

    /**
     * @group legacy
     */
    public function testResultArrayIsRetrieved(): void
    {
        $this->assertSame($this->array, $this->adapter->getArray());
    }

    public function testAdapterReturnsNumberOfItemsInArray(): void
    {
        $this->assertSame(\count($this->array), $this->adapter->getNbResults());
    }

    public static function dataGetSlice(): \Generator
    {
        yield [2, 10];
        yield [3, 2];
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @dataProvider dataGetSlice
     */
    public function testGetSlice(int $offset, int $length): void
    {
        $this->assertSame(
            \array_slice($this->array, $offset, $length),
            $this->adapter->getSlice($offset, $length)
        );
    }
}
