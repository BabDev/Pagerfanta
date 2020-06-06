<?php

namespace Pagerfanta\Adapter;

/**
 * ArrayAdapter.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class ArrayAdapter implements AdapterInterface
{
    private $array;

    /**
     * Constructor.
     *
     * @param array $array the array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Returns the array.
     *
     * @return array the array
     */
    public function getArray()
    {
        return $this->array;
    }

    public function getNbResults()
    {
        return \count($this->array);
    }

    public function getSlice($offset, $length)
    {
        return \array_slice($this->array, $offset, $length);
    }
}
