<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine MongoDB ODM Aggregation Builder.
 */
class AggregationAdapter implements AdapterInterface
{
    private Builder $aggregationBuilder;

    public function __construct(Builder $aggregationBuilder)
    {
        $this->aggregationBuilder = $aggregationBuilder;
    }

    public function getNbResults(): int
    {
        $aggregationBuilder = clone $this->aggregationBuilder;

        return $aggregationBuilder
            ->hydrate(null)
            ->count('numResults')
            ->getAggregation()
            ->getIterator()
            ->toArray()[0]['numResults'] ?? 0;
    }

    public function getSlice(int $offset, int $length): iterable
    {
        $aggregationBuilder = clone $this->aggregationBuilder;

        return $aggregationBuilder
            ->skip($offset)
            ->limit($length)
            ->getAggregation()
            ->getIterator();
    }
}
