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

use Mandango\Query;

/**
 * MandangoAdapter.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoAdapter implements AdapterInterface
{
    private $query;

    /**
     * Constructor.
     *
     * @param Query $query The query.
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Returns the query.
     *
     * @return Query The query.
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
        return $this->query->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        return $this->query->limit($length)->skip($offset)->all();
    }
}
