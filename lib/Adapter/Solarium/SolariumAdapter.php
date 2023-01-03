<?php declare(strict_types=1);

namespace Pagerfanta\Solarium;

use Pagerfanta\Adapter\AdapterInterface;
use Solarium\Core\Client\ClientInterface;
use Solarium\Core\Client\Endpoint;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

/**
 * Adapter which calculates pagination from a Solarium Query.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class SolariumAdapter implements AdapterInterface
{
    private ClientInterface $client;
    private Query $query;

    private ?Result $resultSet = null;

    /**
     * @var Endpoint|string|null
     */
    private $endpoint;

    /**
     * @phpstan-var int<0, max>|null
     */
    private ?int $resultSetStart = null;

    /**
     * @phpstan-var int<0, max>|null
     */
    private ?int $resultSetRows = null;

    public function __construct(ClientInterface $client, Query $query)
    {
        $this->client = $client;
        $this->query = $query;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->getResultSet()->getNumFound();
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->getResultSet($offset, $length);
    }

    /**
     * @phpstan-param int<0, max>|null $start
     * @phpstan-param int<0, max>|null $rows
     */
    public function getResultSet(?int $start = null, ?int $rows = null): Result
    {
        if ($this->resultSetStartAndRowsAreNotNullAndChange($start, $rows)) {
            $this->resultSetStart = $start;
            $this->resultSetRows = $rows;

            $this->modifyQuery();
            $this->resultSet = null;
        }

        if (!$this->resultSet instanceof Result) {
            $this->resultSet = $this->createResultSet();
        }

        return $this->resultSet;
    }

    /**
     * @phpstan-param int<0, max>|null $start
     * @phpstan-param int<0, max>|null $rows
     */
    private function resultSetStartAndRowsAreNotNullAndChange(?int $start, ?int $rows): bool
    {
        return $this->resultSetStartAndRowsAreNotNull($start, $rows) && $this->resultSetStartAndRowsChange($start, $rows);
    }

    /**
     * @phpstan-param int<0, max>|null $start
     * @phpstan-param int<0, max>|null $rows
     */
    private function resultSetStartAndRowsAreNotNull(?int $start, ?int $rows): bool
    {
        return null !== $start && null !== $rows;
    }

    /**
     * @phpstan-param int<0, max>|null $start
     * @phpstan-param int<0, max>|null $rows
     */
    private function resultSetStartAndRowsChange(?int $start, ?int $rows): bool
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
