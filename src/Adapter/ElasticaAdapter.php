<?php

namespace Pagerfanta\Adapter;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;

class ElasticaAdapter implements AdapterInterface
{
    /**
     * @var Query
     */
    private $query;

    /**
     * @var ResultSet
     */
    private $resultSet;

    /**
     * @var SearchableInterface
     */
    private $searchable;

    /**
     * @var array
     */
    private $options;

    /**
     * @var int|null
     *
     * Used to limit the number of totalHits returned by ES.
     * For more information, see: https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892
     */
    private $maxResults;

    public function __construct(SearchableInterface $searchable, Query $query, array $options = [], $maxResults = null)
    {
        $this->searchable = $searchable;
        $this->query = $query;
        $this->options = $options;
        $this->maxResults = $maxResults;
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
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
     * Returns the Elastica ResultSet. Will return null if getSlice has not yet been
     * called.
     *
     * @return ResultSet|null
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return iterable the slice
     */
    public function getSlice($offset, $length)
    {
        return $this->resultSet = $this->searchable->search($this->query, array_merge($this->options, [
            'from' => $offset,
            'size' => $length,
        ]));
    }
}
