<?php declare(strict_types=1);

namespace Pagerfanta\Tests;

use Pagerfanta\Adapter\CountableAdapterInterface;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\CursorPagerfantaFactory;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

final class CursorPagerfantaFactoryTest extends TestCase
{
    public function testACursorPagerIsCreatedForANonCountableAdapter(): void
    {
        $position = new CursorPosition(new Cursor(['id' => 1]));

        $pager = CursorPagerfantaFactory::create($this->createStub(CursorAdapterInterface::class), 5, $position);

        $this->assertInstanceOf(CursorPagerfanta::class, $pager);
        $this->assertSame(5, $pager->getMaxPerPage());
        $this->assertSame($position, $pager->getCurrentPosition());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testACountableCursorPagerIsCreatedForACountableAdapter(): void
    {
        $position = new CursorPosition(new Cursor(['id' => 1]));

        $pager = CursorPagerfantaFactory::create($this->createMockForIntersectionOfInterfaces([CursorAdapterInterface::class, CountableAdapterInterface::class]), 5, $position);

        $this->assertInstanceOf(CountableCursorPagerfanta::class, $pager);
        $this->assertSame(5, $pager->getMaxPerPage());
        $this->assertSame($position, $pager->getCurrentPosition());
    }
}
