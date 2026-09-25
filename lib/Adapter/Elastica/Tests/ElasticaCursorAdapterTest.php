<?php declare(strict_types=1);

namespace Pagerfanta\Elastica\Tests;

use Elastica\Query;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use Pagerfanta\Adapter\CountingCursorAdapter;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Base64JsonCursorEncoder;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\CursorPagerfantaFactory;
use Pagerfanta\Elastica\ElasticaAdapter;
use Pagerfanta\Elastica\ElasticaCursorAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticaCursorAdapterTest extends TestCase
{
    /**
     * The requests sent to the searchable, as the query body and the search options.
     *
     * @var list<array{0: array<string, mixed>, 1: array<string, mixed>}>
     */
    private array $requests = [];

    /**
     * Creates a searchable which applies the sort, search_after, and size parameters of a query to the given documents.
     *
     * @param list<array<string, int|string>> $documents
     */
    private function createSearchable(array $documents): MockObject&SearchableInterface
    {
        $searchable = $this->createMock(SearchableInterface::class);

        $searchable->method('search')
            ->willReturnCallback(function (Query $query, array $options) use ($documents): ResultSet {
                $body = $query->toArray();
                $this->requests[] = [$body, $options];

                /** @var list<array<string, array{order: 'asc'|'desc'}>> $sortBody */
                $sortBody = $body['sort'];

                /** @var list<array{0: string, 1: 'asc'|'desc'}> $sort */
                $sort = [];

                foreach ($sortBody as $sortField) {
                    foreach ($sortField as $name => $sortOptions) {
                        $sort[] = [$name, $sortOptions['order']];
                    }
                }

                $sortValues = static fn (array $document): array => array_map(static fn (array $field): int|string => $document[$field[0]], $sort);

                $compare = static function (array $a, array $b) use ($sort): int {
                    foreach ($sort as $index => [, $order]) {
                        $result = $a[$index] <=> $b[$index];

                        if (0 !== $result) {
                            return 'desc' === $order ? -$result : $result;
                        }
                    }

                    return 0;
                };

                usort($documents, static fn (array $a, array $b): int => $compare($sortValues($a), $sortValues($b)));

                if (isset($body['search_after'])) {
                    $documents = array_values(array_filter($documents, static fn (array $document): bool => $compare($sortValues($document), $body['search_after']) > 0));
                }

                $results = array_map(
                    static fn (array $document): Result => new Result(['_id' => (string) $document['id'], '_source' => $document, 'sort' => $sortValues($document)]),
                    \array_slice($documents, 0, $body['size']),
                );

                $resultSet = $this->createMock(ResultSet::class);
                $resultSet->method('getResults')->willReturn($results);

                return $resultSet;
            });

        return $searchable;
    }

    /**
     * @param int|string ...$scores The score of each document, the IDs of the documents are assigned in the order given, starting from 1
     *
     * @return list<array<string, int|string>>
     */
    private function createDocuments(int|string ...$scores): array
    {
        $documents = [];

        foreach (array_values($scores) as $index => $score) {
            $documents[] = ['id' => $index + 1, 'score' => $score];
        }

        return $documents;
    }

    /**
     * @param list<Result> $results
     *
     * @return list<int>
     */
    private function ids(array $results): array
    {
        return array_map(static fn (Result $result): int => (int) $result->getId(), $results);
    }

    /**
     * @param CursorPagerfanta<Result>|CountableCursorPagerfanta<Result> $pager
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

        new ElasticaCursorAdapter($this->createSearchable([]), new Query(), []);
    }

    /**
     * @return \Generator<string, array{0: mixed}>
     */
    public static function dataInvalidSortOrders(): \Generator
    {
        yield 'invalid order' => ['sideways'];
        yield 'options without an order' => [['missing' => '_first']];
        yield 'options with an invalid order' => [['order' => 'sideways']];
    }

    #[DataProvider('dataInvalidSortOrders')]
    public function testTheSortOrderMustBeValid(mixed $order): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ElasticaCursorAdapter($this->createSearchable([]), new Query(), ['id' => $order]);
    }

    public function testTheAdapterSupportsBackwardNavigation(): void
    {
        $this->assertTrue((new ElasticaCursorAdapter($this->createSearchable([]), new Query(), ['id' => 'asc']))->supportsBackwardNavigation());
    }

    public function testTheFirstPageIsSearchedWithoutSearchAfter(): void
    {
        $query = new Query();
        $query->setFrom(20);
        $query->setSort(['score' => 'desc']);

        $adapter = new ElasticaCursorAdapter($this->createSearchable($this->createDocuments(10, 20, 30)), $query, ['id' => 'ASC'], ['from' => 20, 'size' => 50, 'routing' => 'user1']);

        $slice = $adapter->getSlice(null, 2);

        $this->assertSame([1, 2], $this->ids($slice->items));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['id' => 2]), $slice->next);
        $this->assertInstanceOf(ResultSet::class, $adapter->getResultSet());

        [$body, $options] = $this->requests[0];

        $this->assertSame([['id' => ['order' => 'asc']]], $body['sort'], 'The sort fields replace the sort of the query');
        $this->assertSame(3, $body['size'], 'One extra document is requested to detect the next page');
        $this->assertArrayNotHasKey('from', $body);
        $this->assertArrayNotHasKey('search_after', $body);
        $this->assertSame(['routing' => 'user1'], $options, 'The other search options are kept');

        $this->assertSame(20, $query->getParam('from'), 'The original query is not modified');
    }

    public function testThePagesAreSearchedRelativeToTheCursor(): void
    {
        $adapter = new ElasticaCursorAdapter($this->createSearchable($this->createDocuments(10, 20, 30, 40, 50)), new Query(), ['id' => 'asc']);

        $next = $adapter->getSlice(new Cursor(['id' => 2]), 2);

        $this->assertSame([3, 4], $this->ids($next->items));
        $this->assertEquals(new Cursor(['id' => 3], Direction::Previous), $next->previous);
        $this->assertEquals(new Cursor(['id' => 4]), $next->next);
        $this->assertSame([2], $this->requests[0][0]['search_after']);

        $previous = $adapter->getSlice(new Cursor(['id' => 5], Direction::Previous), 2);

        $this->assertSame([3, 4], $this->ids($previous->items), 'Documents are returned in the sort order');
        $this->assertEquals(new Cursor(['id' => 3], Direction::Previous), $previous->previous);
        $this->assertEquals(new Cursor(['id' => 4]), $previous->next);
        $this->assertSame([['id' => ['order' => 'desc', 'missing' => '_first']]], $this->requests[1][0]['sort'], 'The sort is reversed for the previous page');
        $this->assertSame([5], $this->requests[1][0]['search_after']);
    }

    public function testThePagesCanBeWalkedForwardAndBackward(): void
    {
        $pager = new CursorPagerfanta(new ElasticaCursorAdapter($this->createSearchable($this->createDocuments(10, 20, 30, 40, 50, 60, 70)), new Query(), ['id' => 'desc']), 3);

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
        $pager = new CursorPagerfanta(new ElasticaCursorAdapter($this->createSearchable($this->createDocuments(10, 20, 30, 40, 50, 60)), new Query(), ['id' => 'asc']), 3);

        $this->assertSame([[1, 2, 3], [4, 5, 6]], $this->walk($pager)['forward']);
    }

    public function testAnEmptyResultHasNoPages(): void
    {
        $pager = new CursorPagerfanta(new ElasticaCursorAdapter($this->createSearchable([]), new Query(), ['id' => 'asc']), 3);

        $this->assertSame([], $pager->getCurrentPageResults());
        $this->assertFalse($pager->haveToPaginate());
    }

    public function testTiesOnTheLeadingSortFieldAreBrokenByTheFollowingFields(): void
    {
        // Sorted by score descending then ID ascending: 2 (90), 1 (80), 3 (80), 5 (80), 6 (80), 4 (70)
        $pager = new CursorPagerfanta(new ElasticaCursorAdapter($this->createSearchable($this->createDocuments(80, 90, 80, 70, 80, 80)), new Query(), ['score' => 'desc', 'id' => 'asc']), 2);

        $this->assertEquals(new Cursor(['score' => 80, 'id' => 1]), $pager->getNextPosition()->cursor);

        $this->assertSame(
            [
                'forward' => [[2, 1], [3, 5], [6, 4]],
                'backward' => [[3, 5], [2, 1]],
            ],
            $this->walk($pager),
        );
    }

    public function testTheSortOptionsAreKeptAndTheMissingOptionIsReversedForThePreviousPage(): void
    {
        $adapter = new ElasticaCursorAdapter(
            $this->createSearchable([]),
            new Query(),
            [
                'published_at' => ['order' => 'desc', 'format' => 'strict_date_optional_time_nanos'],
                'score' => ['order' => 'asc', 'missing' => '_first'],
                'rating' => ['order' => 'asc', 'missing' => 0],
                'id' => 'asc',
            ],
        );

        $adapter->getSlice(new Cursor(['published_at' => '2026-01-01T00:00:00Z', 'score' => 10, 'rating' => 1, 'id' => 1], Direction::Previous), 2);

        $this->assertSame(
            [
                ['published_at' => ['order' => 'asc', 'format' => 'strict_date_optional_time_nanos', 'missing' => '_first']],
                ['score' => ['order' => 'desc', 'missing' => '_last']],
                ['rating' => ['order' => 'desc', 'missing' => 0]],
                ['id' => ['order' => 'desc', 'missing' => '_first']],
            ],
            $this->requests[0][0]['sort'],
        );
        $this->assertSame(['2026-01-01T00:00:00Z', 10, 1, 1], $this->requests[0][0]['search_after'], 'The search_after values follow the order of the sort fields');
    }

    /**
     * @return \Generator<string, array{0: Cursor}>
     */
    public static function dataInvalidCursors(): \Generator
    {
        yield 'unknown field' => [new Cursor(['name' => 'foo'])];
        yield 'missing field' => [new Cursor(['score' => 80])];
        yield 'extra field' => [new Cursor(['score' => 80, 'id' => 1, 'name' => 'foo'])];
    }

    #[DataProvider('dataInvalidCursors')]
    public function testACursorNotMatchingTheSortFieldsIsRejected(Cursor $cursor): void
    {
        $this->expectException(InvalidCursorException::class);

        (new ElasticaCursorAdapter($this->createSearchable([]), new Query(), ['score' => 'desc', 'id' => 'asc']))->getSlice($cursor, 2);
    }

    public function testADocumentWithoutSortValuesIsRejected(): void
    {
        $this->expectException(LogicException::class);

        $resultSet = $this->createMock(ResultSet::class);
        $resultSet->method('getResults')->willReturn([new Result(['_id' => '1', '_source' => []]), new Result(['_id' => '2', '_source' => []])]);

        $searchable = $this->createMock(SearchableInterface::class);
        $searchable->method('search')->willReturn($resultSet);

        (new ElasticaCursorAdapter($searchable, new Query(), ['id' => 'asc']))->getSlice(null, 1);
    }

    public function testTheResultsCanBeCountedWithAnOffsetAdapter(): void
    {
        $query = new Query();
        $searchable = $this->createSearchable($this->createDocuments(10, 20, 30));
        $searchable->method('count')->willReturn(3);

        $pager = CursorPagerfantaFactory::create(new CountingCursorAdapter(new ElasticaCursorAdapter($searchable, $query, ['id' => 'asc']), new ElasticaAdapter($searchable, $query)), 2);

        $this->assertInstanceOf(CountableCursorPagerfanta::class, $pager);
        $this->assertSame(3, $pager->getNbResults());
    }
}
