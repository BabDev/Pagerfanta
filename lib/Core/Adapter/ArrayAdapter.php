<?php

namespace Pagerfanta\Adapter;

/**
 * Adapter which calculates pagination from an array of items.
 */
class ArrayAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    private $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Retrieves the array of items.
     *
     * @return array
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return \count($this->array);
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        return \array_slice($this->array, $offset, $length);
    }
}
