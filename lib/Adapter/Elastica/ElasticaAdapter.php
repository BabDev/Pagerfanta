<?php declare(strict_types=1);

namespace Pagerfanta\Elastica;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Elastica Query.
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

    public function getNbResults(): int
    {
        if (!$this->resultSet) {
            $totalHits = $this->searchable->count($this->query);
        } else {
            $totalHits = $this->resultSet->getTotalHits();
        }

        if (null === $this->maxResults) {
            return $totalHits;
        }

        return min($totalHits, $this->maxResults);
    }

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
