<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\ArrayCursorAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CountingCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Adapter\TransformingCursorAdapter;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\CursorPagerfantaFactory;
use Pagerfanta\Exception\NotValidResultCountException;
use PHPUnit\Framework\TestCase;

final class CountingCursorAdapterTest extends TestCase
{
    public function testTheResultsAreCountedWithACallable(): void
    {
        $adapter = new CountingCursorAdapter(new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([])), static fn (): int => 42);

        $this->assertSame(42, $adapter->getNbResults());
    }

    public function testTheResultsAreCountedWithACountableAdapter(): void
    {
        $inner = new ArrayCursorAdapter(range(1, 5), static fn (int $item): array => ['id' => $item]);

        $adapter = new CountingCursorAdapter(new TransformingCursorAdapter($inner, static fn (int $item): int => $item * 10), $inner);

        $this->assertSame(5, $adapter->getNbResults());
    }

    public function testTheCallableMustNotReturnANegativeCount(): void
    {
        $this->expectException(NotValidResultCountException::class);

        // @phpstan-ignore-next-line argument.type
        (new CountingCursorAdapter(new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([])), static fn (): int => -1))->getNbResults();
    }

    public function testTheSliceAndBackwardNavigationSupportComeFromTheDecoratedAdapter(): void
    {
        $cursor = new Cursor(['id' => 1]);
        $slice = new CursorSlice([2, 3]);

        $adapter = new CountingCursorAdapter(
            new CallbackCursorAdapter(
                static fn (?Cursor $givenCursor, int $limit): CursorSlice => $cursor === $givenCursor && 2 === $limit ? $slice : new CursorSlice([]),
                true,
            ),
            static fn (): int => 3,
        );

        $this->assertTrue($adapter->supportsBackwardNavigation());
        $this->assertSame($slice, $adapter->getSlice($cursor, 2));
    }

    public function testTheFactoryCreatesACountablePager(): void
    {
        $adapter = new CountingCursorAdapter(new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([])), static fn (): int => 0);

        $this->assertInstanceOf(CountableCursorPagerfanta::class, CursorPagerfantaFactory::create($adapter));
    }
}
