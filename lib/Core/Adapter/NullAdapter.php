<?php

namespace Pagerfanta\Adapter;

/**
 * Adapter which generates a null item list based on a number of results.
 */
class NullAdapter implements AdapterInterface
{
    /**
     * @var int
     */
    private $nbResults;

    /**
     * @param int $nbResults Total item count
     */
    public function __construct($nbResults = 0)
    {
        $this->nbResults = (int) $nbResults;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * The following methods are derived from code of the Zend Framework
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        if ($offset >= $this->nbResults) {
            return [];
        }

        return $this->createNullArray($this->calculateNullArrayLength($offset, $length));
    }

    /**
     * @param int $offset
     * @param int $length
     */
    private function calculateNullArrayLength($offset, $length): int
    {
        $remainCount = $this->remainCount($offset);

        if ($length > $remainCount) {
            return $remainCount;
        }

        return $length;
    }

    /**
     * @param int $offset
     */
    private function remainCount($offset): int
    {
        return $this->nbResults - $offset;
    }

    private function createNullArray(int $length): array
    {
        return array_fill(0, $length, null);
    }
}
