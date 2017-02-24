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

use Propel\Runtime\ActiveQuery\ModelCriteria;

/**
 * Propel2Adapter.
 *
 * @author Raphael YAN <raphael.yan89@gmail.com>
 */
class Propel2Adapter implements AdapterInterface
{
    /**
     * @var ModelCriteria $query
     */
    private $query;

    /**
     * Constructor.
     *
     * @param ModelCriteria $query
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

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        $q = clone $this->getQuery();

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
