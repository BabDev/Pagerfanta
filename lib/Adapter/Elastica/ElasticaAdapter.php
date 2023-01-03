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
    private ?ResultSet $resultSet = null;

    /**
     * @param int|null $maxResults Limit the number of totalHits returned by ElasticSearch; see https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892
     */
    public function __construct(
        private readonly SearchableInterface $searchable,
        private readonly Query $query,
        private readonly array $options = [],
        private readonly ?int $maxResults = null
    ) {
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
