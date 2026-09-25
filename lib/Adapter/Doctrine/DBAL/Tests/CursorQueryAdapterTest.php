<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL\Tests;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\CountingCursorAdapter;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Base64JsonCursorEncoder;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\CursorPagerfantaFactory;
use Pagerfanta\Doctrine\DBAL\CursorQueryAdapter;
use Pagerfanta\Doctrine\DBAL\SingleTableQueryAdapter;
use Pagerfanta\Doctrine\DBAL\SortColumn;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\Attributes\DataProvider;

final class CursorQueryAdapterTest extends DBALTestCase
{
    private function createPostsQueryBuilder(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select('p.*')
            ->from('posts', 'p');
    }

    /**
     * @param CursorPagerfanta<array<string, mixed>>|CountableCursorPagerfanta<array<string, mixed>> $pager
     *
     * @return array{forward: list<list<mixed>>, backward: list<list<mixed>>}
     */
    private function walk(CursorPagerfanta|CountableCursorPagerfanta $pager): array
    {
        $encoder = new Base64JsonCursorEncoder();

        $forward = [array_column($pager->getCurrentPageResults(), 'id')];

        while ($pager->hasNextPage()) {
            $pager = $pager->withPosition(new CursorPosition($encoder->decode($encoder->encode($pager->getNextPosition()->cursor))));
            $forward[] = array_column($pager->getCurrentPageResults(), 'id');
        }

        $backward = [];

        while ($pager->hasPreviousPage()) {
            $pager = $pager->withPosition(new CursorPosition($encoder->decode($encoder->encode($pager->getPreviousPosition()->cursor))));
            $backward[] = array_column($pager->getCurrentPageResults(), 'id');
        }

        return ['forward' => $forward, 'backward' => $backward];
    }

    public function testAtLeastOneSortColumnIsRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CursorQueryAdapter($this->createPostsQueryBuilder(), []);
    }

    public function testASortColumnCanOnlyBeGivenOnce(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CursorQueryAdapter($this->createPostsQueryBuilder(), [new SortColumn('p.id'), new SortColumn('p.id', 'DESC')]);
    }

    public function testTheAdapterSupportsBackwardNavigation(): void
    {
        $this->assertTrue((new CursorQueryAdapter($this->createPostsQueryBuilder(), [new SortColumn('p.id')]))->supportsBackwardNavigation());
    }

    public function testTheFirstPageIsReturnedWithoutACursor(): void
    {
        $slice = (new CursorQueryAdapter($this->createPostsQueryBuilder(), [new SortColumn('p.id')]))->getSlice(null, 3);

        $this->assertSame([1, 2, 3], array_column($slice->items, 'id'));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['p.id' => 3]), $slice->next);
    }

    public function testThePagesAreFetchedRelativeToTheCursor(): void
    {
        $adapter = new CursorQueryAdapter($this->createPostsQueryBuilder(), [new SortColumn('p.id')]);

        $next = $adapter->getSlice(new Cursor(['p.id' => 3]), 3);

        $this->assertSame([4, 5, 6], array_column($next->items, 'id'));
        $this->assertEquals(new Cursor(['p.id' => 4], Direction::Previous), $next->previous);
        $this->assertEquals(new Cursor(['p.id' => 6]), $next->next);

        $previous = $adapter->getSlice(new Cursor(['p.id' => 7], Direction::Previous), 3);

        $this->assertSame([4, 5, 6], array_column($previous->items, 'id'), 'Items are returned in the sort order');
        $this->assertEquals(new Cursor(['p.id' => 4], Direction::Previous), $previous->previous);
        $this->assertEquals(new Cursor(['p.id' => 6]), $previous->next);
    }

    public function testThePreviousPageStopsAtTheStartOfTheList(): void
    {
        $slice = (new CursorQueryAdapter($this->createPostsQueryBuilder(), [new SortColumn('p.id')]))->getSlice(new Cursor(['p.id' => 3], Direction::Previous), 3);

        $this->assertSame([1, 2], array_column($slice->items, 'id'));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['p.id' => 2]), $slice->next);
    }

    public function testThePagesCanBeWalkedForwardAndBackward(): void
    {
        $pager = new CursorPagerfanta(new CursorQueryAdapter($this->createPostsQueryBuilder()->where('p.id <= 7'), [new SortColumn('p.id', 'DESC')]), 3);

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
        $pager = new CursorPagerfanta(new CursorQueryAdapter($this->createPostsQueryBuilder()->where('p.id <= 6'), [new SortColumn('p.id')]), 3);

        $this->assertSame([[1, 2, 3], [4, 5, 6]], $this->walk($pager)['forward']);
    }

    public function testAnEmptyResultSetHasNoPages(): void
    {
        $pager = new CursorPagerfanta(new CursorQueryAdapter($this->createPostsQueryBuilder()->where('p.id > 1000'), [new SortColumn('p.id')]), 3);

        $this->assertSame([], $pager->getCurrentPageResults());
        $this->assertFalse($pager->haveToPaginate());
    }

    public function testTiesOnTheLeadingSortColumnAreBrokenByTheFollowingColumns(): void
    {
        // Each post has 5 comments, sorted by post descending then comment ID ascending: post 2 has comments 6-10 and post 1 has comments 1-5
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('c.id', 'c.post_id')
            ->from('comments', 'c')
            ->where('c.post_id <= 2');

        $pager = new CursorPagerfanta(new CursorQueryAdapter($queryBuilder, [new SortColumn('c.post_id', 'DESC'), new SortColumn('c.id')]), 3);

        $this->assertEquals(new Cursor(['c.post_id' => 2, 'c.id' => 8]), $pager->getNextPosition()->cursor);

        $this->assertSame(
            [
                'forward' => [[6, 7, 8], [9, 10, 1], [2, 3, 4], [5]],
                'backward' => [[2, 3, 4], [9, 10, 1], [6, 7, 8]],
            ],
            $this->walk($pager),
        );
    }

    public function testTheSortColumnsReplaceTheOrderByClauseAndTheQueryParametersAreKept(): void
    {
        $queryBuilder = $this->createPostsQueryBuilder()
            ->where('p.id > :min_id')
            ->andWhere('p.id <= 10 OR p.id = 50')
            ->orderBy('p.post_content', 'DESC')
            ->setParameter('min_id', 5);

        $pager = new CursorPagerfanta(new CursorQueryAdapter($queryBuilder, [new SortColumn('p.id')]), 3);

        $this->assertSame(
            [
                'forward' => [[6, 7, 8], [9, 10, 50]],
                'backward' => [[6, 7, 8]],
            ],
            $this->walk($pager),
        );
    }

    public function testTheOriginalQueryBuilderIsNotModified(): void
    {
        $queryBuilder = $this->createPostsQueryBuilder();
        $sql = $queryBuilder->getSQL();

        (new CursorQueryAdapter($queryBuilder, [new SortColumn('p.id')]))->getSlice(new Cursor(['p.id' => 3]), 3);

        $this->assertSame($sql, $queryBuilder->getSQL());
        $this->assertSame([], $queryBuilder->getParameters());
    }

    public function testAResultKeyCanBeSetForAnAliasedColumn(): void
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('p.id AS post_id')
            ->from('posts', 'p');

        $slice = (new CursorQueryAdapter($queryBuilder, [new SortColumn('p.id', 'ASC', 'post_id')]))->getSlice(null, 2);

        $this->assertEquals(new Cursor(['p.id' => 2]), $slice->next);
    }

    public function testASortColumnMissingFromTheResultIsRejected(): void
    {
        $this->expectException(LogicException::class);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('p.username')
            ->from('posts', 'p');

        (new CursorQueryAdapter($queryBuilder, [new SortColumn('p.id')]))->getSlice(null, 2);
    }

    public function testTheReservedParameterNamesCannotBeUsedByTheQuery(): void
    {
        $this->expectException(LogicException::class);

        $queryBuilder = $this->createPostsQueryBuilder()
            ->where('p.id > :pagerfanta_cursor_0')
            ->setParameter('pagerfanta_cursor_0', 1);

        (new CursorQueryAdapter($queryBuilder, [new SortColumn('p.id')]))->getSlice(new Cursor(['p.id' => 3]), 2);
    }

    /**
     * @return \Generator<string, array{0: Cursor}>
     */
    public static function dataInvalidCursors(): \Generator
    {
        yield 'unknown field' => [new Cursor(['p.username' => 'Jon Doe'])];
        yield 'missing field' => [new Cursor(['c.post_id' => 2])];
        yield 'extra field' => [new Cursor(['c.post_id' => 2, 'c.id' => 8, 'c.username' => 'Jon Doe'])];
        yield 'null value' => [new Cursor(['c.post_id' => null, 'c.id' => 8])];
    }

    #[DataProvider('dataInvalidCursors')]
    public function testACursorNotMatchingTheSortColumnsIsRejected(Cursor $cursor): void
    {
        $this->expectException(InvalidCursorException::class);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('c.id', 'c.post_id')
            ->from('comments', 'c');

        (new CursorQueryAdapter($queryBuilder, [new SortColumn('c.post_id', 'DESC'), new SortColumn('c.id')]))->getSlice($cursor, 3);
    }

    public function testTheResultsCanBeCountedWithAnOffsetAdapter(): void
    {
        $queryBuilder = $this->createPostsQueryBuilder()->where('p.id <= 7');

        $pager = CursorPagerfantaFactory::create(new CountingCursorAdapter(new CursorQueryAdapter($queryBuilder, [new SortColumn('p.id')]), new SingleTableQueryAdapter($queryBuilder, 'p.id')), 3);

        $this->assertInstanceOf(CountableCursorPagerfanta::class, $pager);
        $this->assertSame(7, $pager->getNbResults());
    }
}
