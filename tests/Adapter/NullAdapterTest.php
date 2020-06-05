<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\NullAdapter;
use PHPUnit\Framework\TestCase;

class NullAdapterTest extends TestCase
{
    public function testGetNbResults(): void
    {
        $adapter = new NullAdapter(33);
        $this->assertSame(33, $adapter->getNbResults());
    }

    public function testGetSliceShouldReturnAnEmptyArrayIfTheOffsetIsEqualThanTheNbResults(): void
    {
        $adapter = new NullAdapter(10);
        $this->assertSame([], $adapter->getSlice(10, 5));
    }

    public function testGetSliceShouldReturnAnEmptyArrayIfTheOffsetIsGreaterThanTheNbResults(): void
    {
        $adapter = new NullAdapter(10);
        $this->assertSame([], $adapter->getSlice(11, 5));
    }

    public function testGetSliceShouldReturnANullArrayWithTheLengthPassed(): void
    {
        $adapter = new NullAdapter(100);
        $this->assertSame($this->createNullArray(10), $adapter->getSlice(20, 10));
    }

    public function testGetSliceShouldReturnANullArrayWithTheRemainCountWhenLengthIsGreaterThanTheRemain(): void
    {
        $adapter = new NullAdapter(33);
        $this->assertSame($this->createNullArray(3), $adapter->getSlice(30, 10));
    }

    private function createNullArray($length)
    {
        return array_fill(0, $length, null);
    }
}
