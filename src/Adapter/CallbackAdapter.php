<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class CallbackAdapter implements AdapterInterface
{
    private $getNbResultsCallback;
    private $getSliceCallback;

    /**
     * @param callable $getNbResultsCallback
     * @param callable $getSliceCallback
     */
    public function __construct($getNbResultsCallback, $getSliceCallback)
    {
        if (!\is_callable($getNbResultsCallback)) {
            throw new InvalidArgumentException('$getNbResultsCallback should be a callable');
        }
        if (!\is_callable($getSliceCallback)) {
            throw new InvalidArgumentException('$getSliceCallback should be a callable');
        }

        $this->getNbResultsCallback = $getNbResultsCallback;
        $this->getSliceCallback = $getSliceCallback;
    }

    public function getNbResults()
    {
        return \call_user_func($this->getNbResultsCallback);
    }

    public function getSlice($offset, $length)
    {
        return \call_user_func($this->getSliceCallback, $offset, $length);
    }
}
