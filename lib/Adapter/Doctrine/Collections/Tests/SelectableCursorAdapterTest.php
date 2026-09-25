<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Pagerfanta\Adapter\CountingCursorAdapter;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Base64JsonCursorEncoder;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\CursorPagerfantaFactory;
use Pagerfanta\Doctrine\Collections\SelectableAdapter;
use Pagerfanta\Doctrine\Collections\SelectableCursorAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SelectableCursorAdapterTest extends TestCase
{
    private function createCriteria(): Criteria
    {
        // Raw field access is opt-in for doctrine/collections 2.x, and the flag is deprecated in 3.1 as it is always used
        // @phpstan-ignore-next-line function.alreadyNarrowedType
        return method_exists(Criteria::class, 'isRawFieldValueAccessEnabled') ? Criteria::create(true) : Criteria::create();
    }

    private function descendingOrdering(): mixed
    {
        // The SortDirection enum replaces the Order enum in doctrine/collections 3.1, which replaced strings in 2.2
        // @phpstan-ignore-next-line function.alreadyNarrowedType
        if (enum_exists(\SortDirection::class) && method_exists(Criteria::class, 'getOrderings') && (new \ReflectionMethod(Criteria::class, 'getOrderings'))->hasReturnType()) {
            return \SortDirection::Descending;
        }

        return enum_exists(Order::class) ? Order::Descending : 'DESC';
    }

    /**
     * @param int|null ...$scores The score of each item, the IDs of the items are assigned in the order given, starting from 1
     *
     * @return ArrayCollection<int, Item>
     */
    private function createCollection(?int ...$scores): ArrayCollection
    {
        $items = [];

        foreach (array_values($scores) as $index => $score) {
            $items[] = new Item($index + 1, $score);
        }

        // Shuffle the items to ensure the adapter sorts them
        shuffle($items);

        return new ArrayCollection($items);
    }

    /**
     * @param list<Item> $items
     *
     * @return list<int>
     */
    private function ids(array $items): array
    {
        return array_map(static fn (Item $item): int => $item->getId(), $items);
    }

    /**
     * @param CursorPagerfanta<Item>|CountableCursorPagerfanta<Item> $pager
     *
     * @return array{forward: list<list<int>>, backward: list<list<int>>}
     */
    private function walk(CursorPagerfanta|CountableCursorPagerfanta $pager): array
    {
        $encoder = new Base64JsonCursorEncoder();

        $forward = [$this->ids($pager->getCurrentPageResults())];

        while ($pager->hasNextPage()) {
            $pager = $pager->withPosition(new CursorPosition($encoder->decode($encoder->encode($pager->getNextPosition()->cursor))));
            $forward[] = $this->ids($pager->getCurrentPageResults());
        }

        $backward = [];

        while ($pager->hasPreviousPage()) {
            $pager = $pager->withPosition(new CursorPosition($encoder->decode($encoder->encode($pager->getPreviousPosition()->cursor))));
            $backward[] = $this->ids($pager->getCurrentPageResults());
        }

        return ['forward' => $forward, 'backward' => $backward];
    }

    public function testAtLeastOneSortFieldIsRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SelectableCursorAdapter(new ArrayCollection(), $this->createCriteria(), []);
    }

    public function testTheSortOrderMustBeValid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore-next-line argument.type
        new SelectableCursorAdapter(new ArrayCollection(), $this->createCriteria(), ['id' => 'SIDEWAYS']);
    }

    public function testTheAdapterSupportsBackwardNavigation(): void
    {
        $this->assertTrue((new SelectableCursorAdapter(new ArrayCollection(), $this->createCriteria(), ['id' => 'ASC']))->supportsBackwardNavigation());
    }

    public function testTheFirstPageIsReturnedWithoutACursor(): void
    {
        $slice = (new SelectableCursorAdapter($this->createCollection(10, 20, 30, 40, 50), $this->createCriteria(), ['id' => 'ASC']))->getSlice(null, 2);

        $this->assertSame([1, 2], $this->ids($slice->items));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['id' => 2]), $slice->next);
    }

    public function testThePagesAreFetchedRelativeToTheCursor(): void
    {
        $adapter = new SelectableCursorAdapter($this->createCollection(10, 20, 30, 40, 50), $this->createCriteria(), ['id' => 'asc']);

        $next = $adapter->getSlice(new Cursor(['id' => 2]), 2);

        $this->assertSame([3, 4], $this->ids($next->items));
        $this->assertEquals(new Cursor(['id' => 3], Direction::Previous), $next->previous);
        $this->assertEquals(new Cursor(['id' => 4]), $next->next);

        $previous = $adapter->getSlice(new Cursor(['id' => 5], Direction::Previous), 2);

        $this->assertSame([3, 4], $this->ids($previous->items), 'Items are returned in the sort order');
        $this->assertEquals(new Cursor(['id' => 3], Direction::Previous), $previous->previous);
        $this->assertEquals(new Cursor(['id' => 4]), $previous->next);
    }

    public function testThePagesCanBeWalkedForwardAndBackward(): void
    {
        $pager = new CursorPagerfanta(new SelectableCursorAdapter($this->createCollection(10, 20, 30, 40, 50, 60, 70), $this->createCriteria(), ['id' => 'DESC']), 3);

        $this->assertSame(
            [
                'forward' => [[7, 6, 5], [4, 3, 2], [1]],
                'backward' => [[4, 3, 2], [7, 6, 5]],
            ],
            $this->walk($pager),
        );
    }

    public function testAnExactMultipleOfTheLimitHasNoPhantomNextPage(): void
    {
        $pager = new CursorPagerfanta(new SelectableCursorAdapter($this->createCollection(10, 20, 30, 40, 50, 60), $this->createCriteria(), ['id' => 'ASC']), 3);

        $this->assertSame([[1, 2, 3], [4, 5, 6]], $this->walk($pager)['forward']);
    }

    public function testAnEmptyCollectionHasNoPages(): void
    {
        $pager = new CursorPagerfanta(new SelectableCursorAdapter(new ArrayCollection(), $this->createCriteria(), ['id' => 'ASC']), 3);

        $this->assertSame([], $pager->getCurrentPageResults());
        $this->assertFalse($pager->haveToPaginate());
    }

    public function testTiesOnTheLeadingSortFieldAreBrokenByTheFollowingFields(): void
    {
        // Sorted by score descending then ID ascending: 2 (90), 1 (80), 3 (80), 5 (80), 6 (80), 4 (70)
        $pager = new CursorPagerfanta(new SelectableCursorAdapter($this->createCollection(80, 90, 80, 70, 80, 80), $this->createCriteria(), ['score' => 'DESC', 'id' => 'ASC']), 2);

        $this->assertEquals(new Cursor(['score' => 80, 'id' => 1]), $pager->getNextPosition()->cursor);

        $this->assertSame(
            [
                'forward' => [[2, 1], [3, 5], [6, 4]],
                'backward' => [[3, 5], [2, 1]],
            ],
            $this->walk($pager),
        );
    }

    public function testTheSortFieldsReplaceTheOrderingsAndTheCriteriaAreKept(): void
    {
        $criteria = $this->createCriteria()
            ->where(Criteria::expr()->gt('score', 20))
            ->setFirstResult(1)
            ->setMaxResults(1);

        $criteria->orderBy(['score' => $this->descendingOrdering()]);

        $pager = new CursorPagerfanta(new SelectableCursorAdapter($this->createCollection(10, 20, 30, 40, 50, 60, 70), $criteria, ['id' => 'ASC']), 2);

        $this->assertSame(
            [
                'forward' => [[3, 4], [5, 6], [7]],
                'backward' => [[5, 6], [3, 4]],
            ],
            $this->walk($pager),
        );
    }

    public function testTheSortOrderCanBeGivenAsAnOrderEnum(): void
    {
        if (!enum_exists(Order::class)) {
            $this->markTestSkipped('The Order enum requires doctrine/collections 2.2 or later.');
        }

        $pager = new CursorPagerfanta(new SelectableCursorAdapter($this->createCollection(10, 20, 30), $this->createCriteria(), ['id' => Order::Descending]), 2);

        $this->assertSame([[3, 2], [1]], $this->walk($pager)['forward']);
    }

    public function testTheSortOrderCanBeGivenAsASortDirectionEnum(): void
    {
        if (!enum_exists(\SortDirection::class)) {
            $this->markTestSkipped('The SortDirection enum requires PHP 8.6 or symfony/polyfill-php86.');
        }

        $pager = new CursorPagerfanta(new SelectableCursorAdapter($this->createCollection(10, 20, 30), $this->createCriteria(), ['id' => \SortDirection::Descending]), 2);

        $this->assertSame([[3, 2], [1]], $this->walk($pager)['forward']);
    }

    public function testArraysCanBePaginatedWithASingleSortField(): void
    {
        // Multiple sort fields are not tested with arrays as doctrine/collections 3.x does not support arrays in composite expressions
        $collection = new ArrayCollection([['id' => 3], ['id' => 1], ['id' => 2]]);

        $adapter = new SelectableCursorAdapter($collection, $this->createCriteria(), ['id' => 'ASC']);

        $first = $adapter->getSlice(null, 2);

        $this->assertSame([1, 2], array_column($first->items, 'id'));
        $this->assertEquals(new Cursor(['id' => 2]), $first->next);
        $this->assertSame([3], array_column($adapter->getSlice($first->next, 2)->items, 'id'));
    }

    public function testANullSortFieldValueIsRejected(): void
    {
        $this->expectException(LogicException::class);

        (new SelectableCursorAdapter($this->createCollection(10, null), $this->createCriteria(), ['score' => 'ASC', 'id' => 'ASC']))->getSlice(null, 1);
    }

    /**
     * @return \Generator<string, array{0: Cursor}>
     */
    public static function dataInvalidCursors(): \Generator
    {
        yield 'unknown field' => [new Cursor(['name' => 'foo'])];
        yield 'missing field' => [new Cursor(['score' => 80])];
        yield 'extra field' => [new Cursor(['score' => 80, 'id' => 1, 'name' => 'foo'])];
        yield 'null value' => [new Cursor(['score' => null, 'id' => 1])];
    }

    #[DataProvider('dataInvalidCursors')]
    public function testACursorNotMatchingTheSortFieldsIsRejected(Cursor $cursor): void
    {
        $this->expectException(InvalidCursorException::class);

        (new SelectableCursorAdapter($this->createCollection(80, 90), $this->createCriteria(), ['score' => 'DESC', 'id' => 'ASC']))->getSlice($cursor, 2);
    }

    public function testTheResultsCanBeCountedWithAnOffsetAdapter(): void
    {
        $collection = $this->createCollection(10, 20, 30, 40, 50);
        $criteria = $this->createCriteria()->where(Criteria::expr()->gt('score', 20));

        $pager = CursorPagerfantaFactory::create(new CountingCursorAdapter(new SelectableCursorAdapter($collection, $criteria, ['id' => 'ASC']), new SelectableAdapter($collection, $criteria)), 2);

        $this->assertInstanceOf(CountableCursorPagerfanta::class, $pager);
        $this->assertSame(3, $pager->getNbResults());
    }
}
