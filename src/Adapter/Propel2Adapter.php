<?php

namespace Pagerfanta\Adapter;

use Propel\Runtime\ActiveQuery\ModelCriteria;

trigger_deprecation('babdev/pagerfanta', '2.2', 'The "%s" adapter is deprecated and will be removed in 3.0.', Propel2Adapter::class);

/**
 * Propel2Adapter.
 *
 * @author Raphael YAN <raphael.yan89@gmail.com>
 * @deprecated to be removed in 3.0
 */
class Propel2Adapter implements AdapterInterface
{
    /**
     * @var ModelCriteria
     */
    private $query;

    /**
     * Constructor.
     */
    public function __construct(ModelCriteria $query)
    {
        $this->query = $query;
    }

    /**
     * Returns the query.
     *
     * @return ModelCriteria
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function getNbResults()
    {
        $q = clone $this->getQuery();

        $q->offset(0);

        return $q->count();
    }

    public function getSlice($offset, $length)
    {
        $q = clone $this->getQuery();

        $q->limit($length);
        $q->offset($offset);

        return $q->find();
    }
}
