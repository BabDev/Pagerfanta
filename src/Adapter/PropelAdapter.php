<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta\Adapter;

/**
 * PropelAdapter.
 *
 * @author William DURAND <william.durand1@gmail.com>
 */
class PropelAdapter implements AdapterInterface
{
    private $query;

    /**
     * Constructor.
     *
     * @param \ModelCriteria $query
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Returns the query.
     *
     * @return \ModelCriteria
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        $q = clone $this->getQuery();

        $q->limit(0);
        $q->offset(0);

        return $q->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $q = clone $this->getQuery();

        $q->limit($length);
        $q->offset($offset);

        return $q->find();
    }
}
