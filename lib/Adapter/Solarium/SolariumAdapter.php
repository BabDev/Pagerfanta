<?php declare(strict_types=1);

namespace Pagerfanta\Solarium;

use Pagerfanta\Adapter\AdapterInterface;
use Solarium\Core\Client\ClientInterface;
use Solarium\Core\Client\Endpoint;
use Solarium\Core\Query\DocumentInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

/**
 * Adapter which calculates pagination from a Solarium Query.
 *
 * @implements AdapterInterface<DocumentInterface>
 */
class SolariumAdapter implements AdapterInterface
{
    private ?Result $resultSet = null;

    private Endpoint|string|null $endpoint = null;

    /**
     * @var int<0, max>|null
     */
    private ?int $resultSetStart = null;

    /**
     * @var int<0, max>|null
     */
    private ?int $resultSetRows = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly Query $query,
    ) {}

    /**
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->getResultSet()->getNumFound();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->getResultSet($offset, $length);
    }

    /**
     * @param int<0, max>|null $start
     * @param int<0, max>|null $rows
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
     * @param int<0, max>|null $start
     * @param int<0, max>|null $rows
     *
     * @phpstan-assert-if-true int<0, max> $start
     * @phpstan-assert-if-true int<0, max> $rows
     */
    private function resultSetStartAndRowsAreNotNullAndChange(?int $start, ?int $rows): bool
    {
        return $this->resultSetStartAndRowsAreNotNull($start, $rows) && $this->resultSetStartAndRowsChange($start, $rows);
    }

    /**
     * @param int<0, max>|null $start
     * @param int<0, max>|null $rows
     *
     * @phpstan-assert-if-true int<0, max> $start
     * @phpstan-assert-if-true int<0, max> $rows
     */
    private function resultSetStartAndRowsAreNotNull(?int $start, ?int $rows): bool
    {
        return null !== $start && null !== $rows;
    }

    /**
     * @param int<0, max> $start
     * @param int<0, max> $rows
     */
    private function resultSetStartAndRowsChange(int $start, int $rows): bool
    {
        return $start !== $this->resultSetStart || $rows !== $this->resultSetRows;
    }

    private function modifyQuery(): void
    {
        \assert(null !== $this->resultSetStart);
        \assert(null !== $this->resultSetRows);

        $this->query->setStart($this->resultSetStart)
            ->setRows($this->resultSetRows);
    }

    private function createResultSet(): Result
    {
        return $this->client->select($this->query, $this->endpoint);
    }

    public function setEndpoint(Endpoint|string|null $endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
