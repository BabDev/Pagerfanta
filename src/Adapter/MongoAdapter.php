<?php

namespace Pagerfanta\Adapter;

trigger_deprecation('babdev/pagerfanta', '2.2', 'The "%s" adapter is deprecated and will be removed in 3.0.', MongoAdapter::class);

/**
 * MongoAdapter.
 *
 * @author Sergey Ponomaryov <serg.ponomaryov@gmail.com>
 * @deprecated to be removed in 3.0
 */
class MongoAdapter implements AdapterInterface
{
    /**
     * @var \MongoCursor
     */
    private $cursor;

    public function __construct(\MongoCursor $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * @return \MongoCursor
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
