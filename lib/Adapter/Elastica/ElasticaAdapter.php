<?php

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
    /**
     * @var SearchableInterface
     */
    private $searchable;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var array
     */
    private $options;

    /**
     * Used to limit the number of totalHits returned by ElasticSearch.
     * For more information, see: https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892.
     *
     * @var int|null
     */
    private $maxResults;

    /**
     * @var ResultSet|null
     */
    private $resultSet;

    /**
     * @param int|null $maxResults
     */
    public function __construct(SearchableInterface $searchable, Query $query, array $options = [], $maxResults = null)
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
     *
     * @return ResultSet|null
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * @return int
     */
    public function getNbResults()
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

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
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
