<?php

namespace Pagerfanta\Adapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.2', 'The "%s" adapter is deprecated and will be removed in 3.0.', PropelAdapter::class);

/**
 * Adapter which calculates pagination from a Propel ModelCriteria.
 *
 * @deprecated to be removed in 3.0
 */
class PropelAdapter implements AdapterInterface
{
    /**
     * @var \ModelCriteria
     */
    private $query;

    public function __construct(\ModelCriteria $query)
    {
        $this->query = $query;
    }

    /**
     * @return \ModelCriteria
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

        $q->limit(0);
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
