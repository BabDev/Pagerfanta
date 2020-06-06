<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta\Adapter;

use Pagerfanta\Exception\InvalidArgumentException;
use Solarium\Core\Client\Client;
use Solarium\Core\Client\Endpoint;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

/**
 * SolariumAdapter.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class SolariumAdapter implements AdapterInterface
{
    /**
     * @var Client|\Solarium_Client
     */
    private $client;

    /**
     * @var Query|\Solarium_Query_Select
     */
    private $query;

    /**
     * @var Result|\Solarium_Result_Select
     */
    private $resultSet;

    /**
     * @var Endpoint|string|null
     */
    private $endPoint;

    /**
     * @var int|null
     */
    private $resultSetStart;

    /**
     * @var int|null
     */
    private $resultSetRows;

    /**
     * Constructor.
     *
     * @param \Solarium_Client|Client      $client a Solarium client
     * @param \Solarium_Query_Select|Query $query  a Solarium select query
     */
    public function __construct($client, $query)
    {
        $this->checkClient($client);
        $this->checkQuery($query);

        $this->client = $client;
        $this->query = $query;
    }

    /**
     * @param \Solarium_Client|Client $client
     *
     * @throws InvalidArgumentException if the client is not a proper class instance
     */
    private function checkClient($client): void
    {
        if ($this->isClientInvalid($client)) {
            throw new InvalidArgumentException($this->getClientInvalidMessage($client));
        }

        if ($client instanceof \Solarium_Client) {
            trigger_deprecation(
                'babdev/pagerfanta',
                '2.2',
                'Support for solarium/solarium 2.x is deprecated, as of 3.0 the minimum supported version will be solarium/solarium 4.0.'
            );
        }
    }

    /**
     * @param \Solarium_Client|Client $client
     */
    private function isClientInvalid($client): bool
    {
        return !($client instanceof Client) && !($client instanceof \Solarium_Client);
    }

    /**
     * @param \Solarium_Client|Client $client
     */
    private function getClientInvalidMessage($client): string
    {
        return sprintf(
            'The client object should be a %s or %s instance, %s given',
            \Solarium_Client::class,
            Client::class,
            'object' === gettype($client) ? \get_class($client) : gettype($client)
        );
    }

    /**
     * @param \Solarium_Query_Select|Query $query
     *
     * @throws InvalidArgumentException if the query is not a proper class instance
     */
    private function checkQuery($query): void
    {
        if ($this->isQueryInvalid($query)) {
            throw new InvalidArgumentException($this->getQueryInvalidMessage($query));
        }

        if ($query instanceof \Solarium_Query_Select) {
            trigger_deprecation(
                'babdev/pagerfanta',
                '2.2',
                'Support for solarium/solarium 2.x is deprecated, as of 3.0 the minimum supported version will be solarium/solarium 4.0.'
            );
        }
    }

    /**
     * @param \Solarium_Query_Select|Query $query
     */
    private function isQueryInvalid($query): bool
    {
        return !($query instanceof Query) && !($query instanceof \Solarium_Query_Select);
    }

    /**
     * @param \Solarium_Query_Select|Query $query
     */
    private function getQueryInvalidMessage($query): string
    {
        return sprintf(
            'The query object should be a %s or %s instance, %s given',
            \Solarium_Query_Select::class,
            Query::class,
            'object' === gettype($query) ? \get_class($query) : gettype($query)
        );
    }

    public function getNbResults()
    {
        return $this->getResultSet()->getNumFound();
    }

    public function getSlice($offset, $length)
    {
        return $this->getResultSet($offset, $length);
    }

    /**
     * @param int $start
     * @param int $rows
     *
     * @return \Solarium_Result_Select|\Solarium\QueryType\Select\Result\Result
     */
    public function getResultSet($start = null, $rows = null)
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
        $this->query
            ->setStart($this->resultSetStart)
            ->setRows($this->resultSetRows);
    }

    /**
     * @return \Solarium_Result_Select|\Solarium\QueryType\Select\Result\Result
     */
    private function createResultSet()
    {
        return $this->client->select($this->query, $this->endPoint);
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
     * @param Endpoint|string|null $endPoint
     *
     * @return $this
     */
    public function setEndPoint($endPoint)
    {
        $this->endPoint = $endPoint;

        return $this;
    }
}
