<?php

namespace Pagerfanta\Adapter;

use Mandango\Query;

trigger_deprecation('pagerfanta/pagerfanta', '2.2', 'The "%s" adapter is deprecated and will be removed in 3.0.', MandangoAdapter::class);

/**
 * Adapter which calculates pagination from a Mandango Query.
 *
 * @deprecated to be removed in 3.0, dependent package is abandoned
 */
class MandangoAdapter implements AdapterInterface
{
    /**
     * @var Query
     */
    private $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->query->count();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        return $this->query->limit($length)
            ->skip($offset)
            ->all();
    }
}
