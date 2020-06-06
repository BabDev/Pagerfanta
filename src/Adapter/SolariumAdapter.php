<?php

namespace Pagerfanta\Adapter;

use Solarium\Core\Client\ClientInterface;
use Solarium\Core\Client\Endpoint;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

/**
 * Adapter which calculates pagination from a Solarium Query.
 */
class SolariumAdapter implements AdapterInterface
{
    private ClientInterface $client;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Result
     */
    private $resultSet;

    /**
     * @var Endpoint|string|null
     */
    private $endpoint;

    /**
     * @var int|null
     */
    private $resultSetStart;

    /**
     * @var int|null
     */
    private $resultSetRows;

    public function __construct(ClientInterface $client, Query $query)
    {
        $this->client = $client;
        $this->query = $query;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->getResultSet()->getNumFound();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        return $this->getResultSet($offset, $length);
    }

    /**
     * @param int $start
     * @param int $rows
     */
    public function getResultSet($start = null, $rows = null): Result
    {
        if ($this->resultSetStartAndRowsAreNotNullAndChange($start, $rows)) {
            $this->resultSetStart = $start;
            $this->resultSetRows = $rows;

            $this->modifyQuery();
            $this->clearResultSet();
        }

        if ($this->resultSetEmpty()) {
            $this->resultSet = $this->createResultSet();
        }

        return $this->resultSet;
    }

    private function resultSetStartAndRowsAreNotNullAndChange($start, $rows): bool
    {
        return $this->resultSetStartAndRowsAreNotNull($start, $rows) && $this->resultSetStartAndRowsChange($start, $rows);
    }

    private function resultSetStartAndRowsAreNotNull($start, $rows): bool
    {
        return null !== $start && null !== $rows;
    }

    private function resultSetStartAndRowsChange($start, $rows): bool
    {
        return $start !== $this->resultSetStart || $rows !== $this->resultSetRows;
    }

    private function modifyQuery(): void
    {
        $this->query->setStart($this->resultSetStart)
            ->setRows($this->resultSetRows);
    }

    private function createResultSet(): Result
    {
        return $this->client->select($this->query, $this->endpoint);
    }

    private function clearResultSet(): void
    {
        $this->resultSet = null;
    }

    private function resultSetEmpty(): bool
    {
        return null === $this->resultSet;
    }

    /**
     * @param Endpoint|string|null $endpoint
     *
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
