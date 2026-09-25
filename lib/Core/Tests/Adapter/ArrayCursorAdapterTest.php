<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\ArrayCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Base64JsonCursorEncoder;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfantaFactory;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ArrayCursorAdapterTest extends TestCase
{
    /**
     * @return ArrayCursorAdapter<array{id: int}>
     */
    private function createAdapter(int $count): ArrayCursorAdapter
    {
        return new ArrayCursorAdapter(
            array_map(static fn (int $id): array => ['id' => $id], $count > 0 ? range(1, $count) : []),
            static fn (array $item): array => ['id' => $item['id']],
        );
    }

    /**
     * @param CursorSlice<array{id: int}> $slice
     *
     * @return list<int>
     */
    private function ids(CursorSlice $slice): array
    {
        return array_column($slice->items, 'id');
    }

    public function testTheAdapterSupportsBackwardNavigation(): void
    {
        $this->assertTrue($this->createAdapter(3)->supportsBackwardNavigation());
    }

    public function testTheAdapterCountsTheItems(): void
    {
        $this->assertSame(7, $this->createAdapter(7)->getNbResults());
    }

    public function testTheFirstPageIsReturnedWithoutACursor(): void
    {
        $slice = $this->createAdapter(7)->getSlice(null, 3);

        $this->assertSame([1, 2, 3], $this->ids($slice));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['id' => 3], Direction::Next), $slice->next);
    }

    public function testTheNextPageFollowsTheCursor(): void
    {
        $slice = $this->createAdapter(7)->getSlice(new Cursor(['id' => 3]), 3);

        $this->assertSame([4, 5, 6], $this->ids($slice));
        $this->assertEquals(new Cursor(['id' => 4], Direction::Previous), $slice->previous);
        $this->assertEquals(new Cursor(['id' => 6], Direction::Next), $slice->next);
    }

    public function testThePreviousPagePrecedesTheCursor(): void
    {
        $slice = $this->createAdapter(7)->getSlice(new Cursor(['id' => 7], Direction::Previous), 3);

        $this->assertSame([4, 5, 6], $this->ids($slice), 'Items are returned in the list order');
        $this->assertEquals(new Cursor(['id' => 4], Direction::Previous), $slice->previous);
        $this->assertEquals(new Cursor(['id' => 6], Direction::Next), $slice->next);
    }

    public function testThePreviousPageStopsAtTheStartOfTheList(): void
    {
        $slice = $this->createAdapter(7)->getSlice(new Cursor(['id' => 3], Direction::Previous), 3);

        $this->assertSame([1, 2], $this->ids($slice));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['id' => 2], Direction::Next), $slice->next);
    }

    public function testThePreviousPageOfTheFirstItemIsEmpty(): void
    {
        $this->assertEquals(new CursorSlice([]), $this->createAdapter(7)->getSlice(new Cursor(['id' => 1], Direction::Previous), 3));
    }

    public function testTheLastPageHasNoNextCursor(): void
    {
        $slice = $this->createAdapter(7)->getSlice(new Cursor(['id' => 6]), 3);

        $this->assertSame([7], $this->ids($slice));
        $this->assertEquals(new Cursor(['id' => 7], Direction::Previous), $slice->previous);
        $this->assertNull($slice->next);
    }

    public function testAnExactMultipleOfTheLimitHasNoPhantomNextPage(): void
    {
        $adapter = $this->createAdapter(6);

        $first = $adapter->getSlice(null, 3);
        $this->assertInstanceOf(Cursor::class, $first->next);

        $second = $adapter->getSlice($first->next, 3);
        $this->assertSame([4, 5, 6], $this->ids($second));
        $this->assertNull($second->next);
    }

    public function testASinglePageListHasNoNeighbouringPages(): void
    {
        $this->assertEquals(new CursorSlice([['id' => 1], ['id' => 2], ['id' => 3]]), $this->createAdapter(3)->getSlice(null, 3));
    }

    public function testAnEmptyListHasNoPages(): void
    {
        $adapter = $this->createAdapter(0);

        $this->assertSame(0, $adapter->getNbResults());
        $this->assertEquals(new CursorSlice([]), $adapter->getSlice(null, 3));
    }

    public function testTiesOnTheLeadingSortFieldAreBrokenByTheFollowingFields(): void
    {
        // Sorted by score descending, then by id ascending
        $items = [
            ['id' => 2, 'score' => 90],
            ['id' => 1, 'score' => 80],
            ['id' => 3, 'score' => 80],
            ['id' => 5, 'score' => 80],
            ['id' => 4, 'score' => 70],
        ];

        $adapter = new ArrayCursorAdapter($items, static fn (array $item): array => ['score' => $item['score'], 'id' => $item['id']]);

        $first = $adapter->getSlice(null, 2);
        $this->assertSame([2, 1], array_column($first->items, 'id'));
        $this->assertEquals(new Cursor(['score' => 80, 'id' => 1]), $first->next);

        $second = $adapter->getSlice($first->next, 2);
        $this->assertSame([3, 5], array_column($second->items, 'id'), 'The page boundary falls between items with the same score');

        $this->assertInstanceOf(Cursor::class, $second->previous);

        $back = $adapter->getSlice($second->previous, 2);
        $this->assertSame([2, 1], array_column($back->items, 'id'));
    }

    public function testThePagesCanBeWalkedForwardAndBackwardThroughEncodedCursors(): void
    {
        $encoder = new Base64JsonCursorEncoder();
        $pager = CursorPagerfantaFactory::create($this->createAdapter(10), 3);

        $this->assertInstanceOf(CountableCursorPagerfanta::class, $pager);
        $this->assertSame(10, $pager->getNbResults());

        $forward = [array_column($pager->getCurrentPageResults(), 'id')];

        while ($pager->hasNextPage()) {
            $cursor = $encoder->decode($encoder->encode($pager->getNextPosition()->cursor));
            $pager = $pager->withPosition(new CursorPosition($cursor));
            $forward[] = array_column($pager->getCurrentPageResults(), 'id');
        }

        $this->assertSame([[1, 2, 3], [4, 5, 6], [7, 8, 9], [10]], $forward);

        $backward = [];

        while ($pager->hasPreviousPage()) {
            $cursor = $encoder->decode($encoder->encode($pager->getPreviousPosition()->cursor));
            $pager = $pager->withPosition(new CursorPosition($cursor));
            $backward[] = array_column($pager->getCurrentPageResults(), 'id');
        }

        $this->assertSame([[7, 8, 9], [4, 5, 6], [1, 2, 3]], $backward);
        $this->assertTrue($pager->hasNextPage());
    }

    /**
     * @return \Generator<string, array{0: Cursor}>
     */
    public static function dataInvalidCursors(): \Generator
    {
        yield 'unknown value' => [new Cursor(['id' => 42])];
        yield 'value of a different type' => [new Cursor(['id' => '3'])];
        yield 'unknown field' => [new Cursor(['uuid' => 3])];
        yield 'extra field' => [new Cursor(['id' => 3, 'score' => 1])];
    }

    #[DataProvider('dataInvalidCursors')]
    public function testACursorNotPointingToAnItemIsRejected(Cursor $cursor): void
    {
        $this->expectException(InvalidCursorException::class);

        $this->createAdapter(7)->getSlice($cursor, 3);
    }

    public function testTheKeyExtractorMustReturnUniqueFields(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $adapter = new ArrayCursorAdapter([['id' => 1, 'score' => 5], ['id' => 2, 'score' => 5]], static fn (array $item): array => ['score' => $item['score']]);
        $adapter->getSlice(new Cursor(['score' => 5]), 1);
    }
}
