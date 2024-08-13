<?php declare(strict_types=1);

namespace Pagerfanta\Elastica;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\NotValidResultCountException;

/**
 * Adapter which calculates pagination from an Elastica Query.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class ElasticaAdapter implements AdapterInterface
{
    /**
     * @phpstan-var int<0, max>|null
     */
    private readonly ?int $maxResults;

    private ?ResultSet $resultSet = null;

    /**
     * @param int|null $maxResults Limit the number of totalHits returned by ElasticSearch; see https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892
     *
     * @throws NotValidResultCountException if the maximum number of results is less than zero
     */
    public function __construct(
        private readonly SearchableInterface $searchable,
        private readonly Query $query,
        private readonly array $options = [],
        ?int $maxResults = null
    ) {
        if (null !== $maxResults && $maxResults < 0) {
            throw new NotValidResultCountException(\sprintf('The maximum number of results for the "%s" constructor must be at least zero.', static::class));
        }

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
        $totalHits = $this->resultSet instanceof ResultSet ? $this->resultSet->getTotalHits() : $this->searchable->count($this->query);

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
