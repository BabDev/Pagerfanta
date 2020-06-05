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
 * MongoAdapter.
 *
 * @author Sergey Ponomaryov <serg.ponomaryov@gmail.com>
 */
class MongoAdapter implements AdapterInterface
{
    private $cursor;

    /**
     * Constructor.
     *
     * @param \MongoCursor $cursor the cursor
     */
    public function __construct(\MongoCursor $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * Returns the cursor.
     *
     * @return \MongoCursor the cursor
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    public function getNbResults()
    {
        return $this->cursor->count();
    }

    public function getSlice($offset, $length)
    {
        $this->cursor->limit($length);
        $this->cursor->skip($offset);

        return $this->cursor;
    }
}
