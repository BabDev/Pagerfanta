<?php declare(strict_types=1);

namespace Pagerfanta\Solarium\Tests;

use Pagerfanta\Adapter\CountingCursorAdapter;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Base64JsonCursorEncoder;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\CursorPagerfantaFactory;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Exception\LogicException;
use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Solarium\SolariumAdapter;
use Pagerfanta\Solarium\SolariumCursorAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Core\Client\ClientInterface;
use Solarium\Core\Query\DocumentInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Document;
use Solarium\QueryType\Select\Result\Result;

final class SolariumCursorAdapterTest extends TestCase
{
    /**
     * The queries sent to the client.
     *
     * @var list<Query>
     */
    private array $queries = [];

    /**
     * Creates a client which simulates Solr's cursorMark behavior for documents with the given IDs.
     *
     * The cursor mark encodes the number of documents before it, and the same mark is returned when there are no more documents.
     *
     * @param bool $reportNumFound Whether the number of documents found is reported
     */
    private function createClient(int $count, bool $reportNumFound = true): MockObject&ClientInterface
    {
        $client = $this->createMock(ClientInterface::class);

        $client->method('select')
            ->willReturnCallback(function (Query $query) use ($count, $reportNumFound): Result {
                $this->queries[] = $query;

                $cursorMark = (string) $query->getCursorMark();
                $start = '*' === $cursorMark ? 0 : (int) substr($cursorMark, \strlen('mark-'));
                $end = min($start + (int) $query->getRows(), $count);

                $documents = [];

                for ($id = $start + 1; $id <= $end; ++$id) {
                    $documents[] = new Document(['id' => $id]);
                }

                $result = $this->createMock(Result::class);
                $result->method('getDocuments')->willReturn($documents);
                $result->method('getNextCursorMark')->willReturn($end === $start ? $cursorMark : 'mark-'.$end);
                $result->method('getNumFound')->willReturn($reportNumFound ? $count : null);

                return $result;
            });

        return $client;
    }

    /**
     * @param list<DocumentInterface> $documents
     *
     * @return list<int>
     */
    private function ids(array $documents): array
    {
        return array_map(static fn (DocumentInterface $document): int => $document->getFields()['id'], $documents);
    }

    /**
     * @param CursorPagerfanta<DocumentInterface>|CountableCursorPagerfanta<DocumentInterface> $pager
     *
     * @return list<list<int>>
     */
    private function walkForward(CursorPagerfanta|CountableCursorPagerfanta $pager): array
    {
        $encoder = new Base64JsonCursorEncoder();

        $pages = [$this->ids($pager->getCurrentPageResults())];

        while ($pager->hasNextPage()) {
            $pager = $pager->withPosition(new CursorPosition($encoder->decode($encoder->encode($pager->getNextPosition()->cursor))));
            $pages[] = $this->ids($pager->getCurrentPageResults());
        }

        $this->assertFalse($pager->hasPreviousPage(), 'A forward-only adapter never has a previous page');

        return $pages;
    }

    public function testTheAdapterDoesNotSupportBackwardNavigation(): void
    {
        $this->assertFalse((new SolariumCursorAdapter($this->createClient(0), new Query()))->supportsBackwardNavigation());
    }

    public function testTheFirstPageIsSelectedWithTheInitialCursorMark(): void
    {
        $query = new Query();
        $query->setStart(20);

        $adapter = new SolariumCursorAdapter($this->createClient(5), $query);

        $slice = $adapter->getSlice(null, 2);

        $this->assertSame([1, 2], $this->ids($slice->items));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['cursorMark' => 'mark-2', 'offset' => 2]), $slice->next);
        $this->assertInstanceOf(Result::class, $adapter->getResultSet());

        $this->assertSame('*', $this->queries[0]->getCursorMark());
        $this->assertSame(0, $this->queries[0]->getStart(), 'Solr cursors require starting from the first row');
        $this->assertSame(2, $this->queries[0]->getRows());

        $this->assertSame(20, $query->getStart(), 'The original query is not modified');
        $this->assertNull($query->getCursorMark());
    }

    public function testTheNextPageIsSelectedWithTheCursorMark(): void
    {
        $slice = (new SolariumCursorAdapter($this->createClient(5), new Query()))->getSlice(new Cursor(['cursorMark' => 'mark-2', 'offset' => 2]), 2);

        $this->assertSame([3, 4], $this->ids($slice->items));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['cursorMark' => 'mark-4', 'offset' => 4]), $slice->next);
        $this->assertSame('mark-2', $this->queries[0]->getCursorMark());
    }

    public function testThePagesCanBeWalkedForward(): void
    {
        $this->assertSame([[1, 2, 3], [4, 5, 6], [7]], $this->walkForward(new CursorPagerfanta(new SolariumCursorAdapter($this->createClient(7), new Query()), 3)));
    }

    public function testAnExactMultipleOfTheLimitHasNoPhantomNextPage(): void
    {
        $this->assertSame([[1, 2, 3], [4, 5, 6]], $this->walkForward(new CursorPagerfanta(new SolariumCursorAdapter($this->createClient(6), new Query()), 3)));
        $this->assertCount(2, $this->queries, 'No request is made for an empty page');
    }

    public function testTheCursorMarkIsUsedWhenTheNumberFoundIsNotReported(): void
    {
        // Without the number found, an exact multiple of the limit can only be detected by requesting the next page
        $this->assertSame([[1, 2, 3], [4, 5, 6], []], $this->walkForward(new CursorPagerfanta(new SolariumCursorAdapter($this->createClient(6, false), new Query()), 3)));
    }

    public function testAnEmptyResultHasNoPages(): void
    {
        $pager = new CursorPagerfanta(new SolariumCursorAdapter($this->createClient(0), new Query()), 3);

        $this->assertSame([], $pager->getCurrentPageResults());
        $this->assertFalse($pager->haveToPaginate());
    }

    public function testAMissingNextCursorMarkIsRejected(): void
    {
        $this->expectException(LogicException::class);

        $result = $this->createMock(Result::class);
        $result->method('getDocuments')->willReturn([new Document(['id' => 1])]);
        $result->method('getNextCursorMark')->willReturn(null);

        $client = $this->createMock(ClientInterface::class);
        $client->method('select')->willReturn($result);

        (new SolariumCursorAdapter($client, new Query()))->getSlice(null, 1);
    }

    /**
     * @return \Generator<string, array{0: Cursor}>
     */
    public static function dataInvalidCursors(): \Generator
    {
        yield 'previous direction' => [new Cursor(['cursorMark' => 'mark-2', 'offset' => 2], Direction::Previous)];
        yield 'missing cursor mark' => [new Cursor(['offset' => 2])];
        yield 'missing offset' => [new Cursor(['cursorMark' => 'mark-2'])];
        yield 'empty cursor mark' => [new Cursor(['cursorMark' => '', 'offset' => 2])];
        yield 'cursor mark not a string' => [new Cursor(['cursorMark' => 2, 'offset' => 2])];
        yield 'offset not an integer' => [new Cursor(['cursorMark' => 'mark-2', 'offset' => '2'])];
        yield 'negative offset' => [new Cursor(['cursorMark' => 'mark-2', 'offset' => -1])];
        yield 'extra field' => [new Cursor(['cursorMark' => 'mark-2', 'offset' => 2, 'id' => 2])];
    }

    #[DataProvider('dataInvalidCursors')]
    public function testAnInvalidCursorIsRejected(Cursor $cursor): void
    {
        $this->expectException(InvalidCursorException::class);

        (new SolariumCursorAdapter($this->createClient(5), new Query()))->getSlice($cursor, 2);
    }

    public function testTheEndpointIsPassedToTheClient(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('getDocuments')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('select')
            ->with($this->isInstanceOf(Query::class), 'replica')
            ->willReturn($result);

        (new SolariumCursorAdapter($client, new Query()))->setEndpoint('replica')->getSlice(null, 2);
    }

    public function testTheResultsCanBeCountedWithAnOffsetAdapter(): void
    {
        $query = new Query();
        $client = $this->createClient(5);

        $pager = CursorPagerfantaFactory::create(new CountingCursorAdapter(new SolariumCursorAdapter($client, $query), new SolariumAdapter($client, $query)), 2);

        $this->assertInstanceOf(CountableCursorPagerfanta::class, $pager);
        $this->assertSame(5, $pager->getNbResults());
    }
}
