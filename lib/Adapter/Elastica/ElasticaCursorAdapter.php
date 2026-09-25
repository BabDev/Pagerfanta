<?php declare(strict_types=1);

namespace Pagerfanta\Elastica;

use Elastica\Query;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Exception\LogicException;

/**
 * Adapter which calculates cursor based pagination from an Elastica Query using the `search_after` parameter.
 *
 * The sort fields replace any sort on the query, and together they must uniquely identify each document (i.e. the last
 * field should be a unique tiebreaker field). The cursor fields are keyed by the name of each sort field and hold the sort
 * values returned by Elasticsearch.
 *
 * Backward navigation is supported by searching with the reversed sort. When reversing, a field's `missing` option is also
 * reversed so documents without a value stay in the same position relative to the others.
 *
 * @implements CursorAdapterInterface<Result>
 */
class ElasticaCursorAdapter implements CursorAdapterInterface
{
    /**
     * @var non-empty-array<string, array{order: 'asc'|'desc'}&array<string, mixed>>
     */
    private readonly array $sortFields;

    private ?ResultSet $resultSet = null;

    /**
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'|array<string, mixed>> $sortFields The fields to sort the documents by, in order of precedence, mapped to their sort order or to their sort options (which must include the "order")
     * @param array<string, mixed>                                          $options    The search options, the "from" and "size" options are set by the adapter
     *
     * @throws InvalidArgumentException if no sort fields are given or a sort order is not valid
     */
    public function __construct(
        private readonly SearchableInterface $searchable,
        private readonly Query $query,
        array $sortFields,
        private readonly array $options = [],
    ) {
        if ([] === $sortFields) {
            throw new InvalidArgumentException('At least one sort field is required.');
        }

        $normalized = [];

        foreach ($sortFields as $field => $options) {
            if (!\is_array($options)) {
                $options = ['order' => $options];
            }

            $order = \is_string($options['order'] ?? null) ? strtolower($options['order']) : null;

            if ('asc' !== $order && 'desc' !== $order) {
                throw new InvalidArgumentException(\sprintf('The order of the "%s" sort field must be "asc" or "desc".', $field));
            }

            $normalized[$field] = ['order' => $order] + $options;
        }

        $this->sortFields = $normalized;
    }

    /**
     * Returns the Elastica ResultSet from the last slice.
     *
     * The results are in reverse sort order if the last slice was for a cursor with the previous direction. Will return
     * null if getSlice has not yet been called.
     */
    public function getResultSet(): ?ResultSet
    {
        return $this->resultSet;
    }

    public function supportsBackwardNavigation(): bool
    {
        return true;
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorSlice<Result>
     *
     * @throws InvalidCursorException if the cursor fields do not match the sort fields
     * @throws LogicException         if a document does not have valid sort values
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        $reverse = $cursor instanceof Cursor && Direction::Previous === $cursor->direction;

        $query = clone $this->query;
        $query->setSort($this->createSort($reverse));

        // Paging with "from" cannot be combined with "search_after"
        $params = $query->getParams();
        unset($params['from']);
        $query->setParams($params);

        $query->setSize($limit + 1);

        if ($cursor instanceof Cursor) {
            $query->setParam('search_after', $this->createSearchAfter($cursor));
        }

        $options = $this->options;
        unset($options['from'], $options['size']);

        $this->resultSet = $this->searchable->search($query, $options);

        return CursorSlice::fromLookahead(array_values($this->resultSet->getResults()), $limit, $cursor, $this->createCursor(...));
    }

    /**
     * @return list<array<string, array<string, mixed>>>
     */
    private function createSort(bool $reverse): array
    {
        $sort = [];

        foreach ($this->sortFields as $field => $options) {
            if ($reverse) {
                $options['order'] = 'asc' === $options['order'] ? 'desc' : 'asc';

                // Documents missing the field are sorted last by default, a custom missing value is kept as-is
                $missing = $options['missing'] ?? '_last';

                if ('_last' === $missing || '_first' === $missing) {
                    $options['missing'] = '_last' === $missing ? '_first' : '_last';
                }
            }

            $sort[] = [$field => $options];
        }

        return $sort;
    }

    /**
     * @return list<scalar|null>
     *
     * @throws InvalidCursorException if the cursor fields do not match the sort fields
     */
    private function createSearchAfter(Cursor $cursor): array
    {
        if (\count($cursor->fields) !== \count($this->sortFields) || [] !== array_diff_key($this->sortFields, $cursor->fields)) {
            throw new InvalidCursorException(\sprintf('The cursor fields must match the sort fields "%s".', implode('", "', array_keys($this->sortFields))));
        }

        $searchAfter = [];

        foreach (array_keys($this->sortFields) as $field) {
            $searchAfter[] = $cursor->fields[$field];
        }

        return $searchAfter;
    }

    /**
     * @throws LogicException if the document does not have a scalar or null sort value for each sort field
     */
    private function createCursor(Result $result, Direction $direction): Cursor
    {
        $sort = $result->getHit()['sort'] ?? null;

        if (!\is_array($sort) || \count($sort) !== \count($this->sortFields)) {
            throw new LogicException(\sprintf('The "%s" document must have a sort value for each sort field.', $result->getId()));
        }

        $fields = [];

        foreach (array_keys($this->sortFields) as $index => $field) {
            $value = $sort[$index] ?? null;

            if (null !== $value && !\is_scalar($value)) {
                throw new LogicException(\sprintf('The sort value for the "%s" field of the "%s" document must be a scalar or null, "%s" given.', $field, $result->getId(), get_debug_type($value)));
            }

            $fields[$field] = $value;
        }

        return new Cursor($fields, $direction);
    }
}
