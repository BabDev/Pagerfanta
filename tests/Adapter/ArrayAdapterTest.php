<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\ArrayAdapter;
use PHPUnit\Framework\TestCase;

class ArrayAdapterTest extends TestCase
{
    /**
     * @var int[]
     */
    private $array;

    /**
     * @var ArrayAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->array = range(1, 100);
        $this->adapter = new ArrayAdapter($this->array);
    }

    public function testGetArray(): void
    {
        $this->assertSame($this->array, $this->adapter->getArray());
    }

    public function testGetNbResults(): void
    {
        $this->assertSame(100, $this->adapter->getNbResults());
    }

    public function getResultsProvider(): array
    {
        return [
            [2, 10],
            [3, 2],
        ];
    }

    /**
     * @dataProvider getResultsProvider
     */
    public function testGetResults(int $offset, int $length): void
    {
        $expected = \array_slice($this->array, $offset, $length);

        $this->assertSame($expected, $this->adapter->getSlice($offset, $length));
    }
}
