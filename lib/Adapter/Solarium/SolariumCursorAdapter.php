<?php declare(strict_types=1);

namespace Pagerfanta\Solarium;

use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Exception\LogicException;
use Solarium\Core\Client\ClientInterface;
use Solarium\Core\Client\Endpoint;
use Solarium\Core\Query\DocumentInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

/**
 * Adapter which calculates cursor based pagination from a Solarium Query using Solr's cursorMark.
 *
 * The query's sort must include the collection's uniqueKey field. Solr cursors only move forward, so this adapter does
 * not support backward navigation.
 *
 * The cursor holds the Solr cursor mark and the number of documents before it, which is compared with the number of
 * documents found to detect whether there is a next page without requesting it.
 *
 * @implements CursorAdapterInterface<DocumentInterface>
 */
class SolariumCursorAdapter implements CursorAdapterInterface
{
    public const FIELD_CURSOR_MARK = 'cursorMark';
    public const FIELD_OFFSET = 'offset';

    private const INITIAL_CURSOR_MARK = '*';

    private ?Result $resultSet = null;

    private Endpoint|string|null $endpoint = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly Query $query,
    ) {}

    /**
     * Returns the Solarium Result from the last slice.
     *
     * Will return null if getSlice has not yet been called.
     */
    public function getResultSet(): ?Result
    {
        return $this->resultSet;
    }

    public function setEndpoint(Endpoint|string|null $endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function supportsBackwardNavigation(): bool
    {
        return false;
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorSlice<DocumentInterface>
     *
     * @throws InvalidCursorException if the cursor is not valid for this adapter
     * @throws LogicException         if Solr does not return a next cursor mark
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        [$cursorMark, $offset] = $cursor instanceof Cursor ? $this->readCursor($cursor) : [self::INITIAL_CURSOR_MARK, 0];

        $query = clone $this->query;
        $query->setStart(0)
            ->setRows($limit)
            ->setCursorMark($cursorMark);

        $this->resultSet = $result = $this->client->select($query, $this->endpoint);

        $documents = array_values($result->getDocuments());

        if ([] === $documents) {
            return new CursorSlice([]);
        }

        $nextCursorMark = $result->getNextCursorMark();

        if (null === $nextCursorMark) {
            throw new LogicException('Solr did not return a next cursor mark, ensure the query is sorted and its sort includes the uniqueKey field.');
        }

        $nextOffset = $offset + \count($documents);
        $numFound = $result->getNumFound();

        // Solr returns the same cursor mark when there are no more documents, the number found avoids requesting an empty page
        $hasNext = $nextCursorMark !== $cursorMark && (null === $numFound ? \count($documents) === $limit : $nextOffset < $numFound);

        return new CursorSlice(
            $documents,
            null,
            $hasNext ? new Cursor([self::FIELD_CURSOR_MARK => $nextCursorMark, self::FIELD_OFFSET => $nextOffset]) : null,
        );
    }

    /**
     * @return array{0: non-empty-string, 1: int<0, max>}
     *
     * @throws InvalidCursorException if the cursor is not valid for this adapter
     */
    private function readCursor(Cursor $cursor): array
    {
        if (Direction::Next !== $cursor->direction) {
            throw new InvalidCursorException('Solr cursors do not support backward navigation.');
        }

        $cursorMark = $cursor->fields[self::FIELD_CURSOR_MARK] ?? null;
        $offset = $cursor->fields[self::FIELD_OFFSET] ?? null;

        if (2 !== \count($cursor->fields) || !\is_string($cursorMark) || '' === $cursorMark || !\is_int($offset) || $offset < 0) {
            throw new InvalidCursorException(\sprintf('The cursor must have a "%s" string field and a non-negative "%s" integer field.', self::FIELD_CURSOR_MARK, self::FIELD_OFFSET));
        }

        return [$cursorMark, $offset];
    }
}
