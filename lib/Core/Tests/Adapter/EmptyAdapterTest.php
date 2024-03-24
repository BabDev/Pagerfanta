<?php declare(strict_types=1);

namespace Adapter;

use Pagerfanta\Adapter\EmptyAdapter;
use PHPUnit\Framework\TestCase;

final class EmptyAdapterTest extends TestCase
{
    public function testGetNbResults(): void
    {
        $adapter = new EmptyAdapter();

        self::assertSame(0, $adapter->getNbResults());
    }

    public function testGetSliceShouldReturnAnEmptyArray(): void
    {
        $adapter = new EmptyAdapter();

        self::assertSame([], $adapter->getSlice(10, 5));
    }
}
