<?php

namespace Pagerfanta\Adapter;

use Propel\Runtime\ActiveQuery\ModelCriteria;

trigger_deprecation('pagerfanta/pagerfanta', '2.2', 'The "%s" adapter is deprecated and will be removed in 3.0.', Propel2Adapter::class);

/**
 * Adapter which calculates pagination from a Propel2 ModelCriteria.
 *
 * @deprecated to be removed in 3.0
 */
class Propel2Adapter implements AdapterInterface
{
    /**
     * @var ModelCriteria
     */
    private $query;

    public function __construct(ModelCriteria $query)
    {
        $this->query = $query;
    }

    /**
     * @return ModelCriteria
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
        $q = clone $this->getQuery();

        $q->offset(0);

        return $q->count();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        $q = clone $this->getQuery();

        $q->limit($length);
        $q->offset($offset);

        return $q->find();
    }
}
