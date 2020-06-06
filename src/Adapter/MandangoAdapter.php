<?php

namespace Pagerfanta\Adapter;

use Mandango\Query;

trigger_deprecation('babdev/pagerfanta', '2.2', 'The "%s" adapter is deprecated and will be removed in 3.0.', MandangoAdapter::class);

/**
 * MandangoAdapter.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @deprecated to be removed in 3.0, dependent package is abandoned
 */
class MandangoAdapter implements AdapterInterface
{
    private $query;

    /**
     * Constructor.
     *
     * @param Query $query the query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Returns the query.
     *
     * @return Query the query
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function getNbResults()
    {
        return $this->query->count();
    }

    public function getSlice($offset, $length)
    {
        return $this->query->limit($length)->skip($offset)->all();
    }
}
