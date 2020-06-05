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
     * @param array $array The array.
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Returns the array.
     *
     * @return array The array.
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return count($this->array);
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        return array_slice($this->array, $offset, $length);
    }
}
