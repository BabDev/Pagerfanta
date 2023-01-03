<?php declare(strict_types=1);

namespace Pagerfanta\Elastica;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from an Elastica Query.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class ElasticaAdapter implements AdapterInterface
{
    private SearchableInterface $searchable;
    private Query $query;
    private array $options;

    /**
     * Used to limit the number of totalHits returned by ElasticSearch.
     * For more information, see: https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892.
     */
    private ?int $maxResults;

    private ?ResultSet $resultSet = null;

    public function __construct(SearchableInterface $searchable, Query $query, array $options = [], ?int $maxResults = null)
    {
        $this->searchable = $searchable;
        $this->query = $query;
        $this->options = $options;
        $this->maxResults = $maxResults;
    }

    /**
     * Returns the Elastica ResultSet.
     *
     * Will return null if getSlice has not yet been called.
     */
    public function getResultSet(): ?ResultSet
    {
        return $this->resultSet;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        $totalHits = null === $this->resultSet ? $this->searchable->count($this->query) : $this->resultSet->getTotalHits();

        if (null === $this->maxResults) {
            return $totalHits;
        }

        return min($totalHits, $this->maxResults);
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->resultSet = $this->searchable->search(
            $this->query,
            array_merge(
                $this->options,
                [
                    'from' => $offset,
                    'size' => $length,
                ]
            )
        );
    }
}
