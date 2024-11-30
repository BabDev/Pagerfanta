<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\TransformingAdapter;
use PHPUnit\Framework\TestCase;

final class TransformingAdapterTest extends TestCase
{
    /**
     * @var list<int<1, 100>>
     */
    private array $array;

    /**
     * @var TransformingAdapter<int<1, 100>, string>
     */
    private TransformingAdapter $adapter;

    protected function setUp(): void
    {
        $this->array = range(1, 100);
        $this->adapter = new TransformingAdapter(
            new ArrayAdapter($this->array),
            static fn (int $item, int $key) => \sprintf('%s => %s', $key, $item)
        );
    }

    public function testAdapterReturnsNumberOfItemsInArray(): void
    {
        $this->assertCount($this->adapter->getNbResults(), $this->array);
    }

    public function testGetSlice(): void
    {
        $this->assertSame(['0 => 4', '1 => 5'], [...$this->adapter->getSlice(3, 2)]);
    }

    public function testCreateFromInvokable(): void
    {
        $this->adapter = new TransformingAdapter(
            new ArrayAdapter($this->array),
            new class {
                public function __invoke(int $item, int $key): string
                {
                    return \sprintf('%s', $item - 100);
                }
            }
        );

        $this->assertSame(['-89', '-88', '-87', '-86', '-85'], [...$this->adapter->getSlice(10, 5)]);
    }
}
