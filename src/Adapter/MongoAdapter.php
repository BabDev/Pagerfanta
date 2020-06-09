<?php

namespace Pagerfanta\Adapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.2', 'The "%s" adapter is deprecated and will be removed in 3.0.', MongoAdapter::class);

/**
 * Adapter which calculates pagination from a MongoCursor.
 *
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

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->cursor->count();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        $this->cursor->limit($length);
        $this->cursor->skip($offset);

        return $this->cursor;
    }
}
