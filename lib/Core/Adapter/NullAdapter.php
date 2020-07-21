<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which generates a null item list based on a number of results.
 */
class NullAdapter implements AdapterInterface
{
    private int $nbResults;

    public function __construct(int $nbResults = 0)
    {
        $this->nbResults = (int) $nbResults;
    }

    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    /**
     * The following methods are derived from code of the Zend Framework
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */
    public function getSlice(int $offset, int $length): iterable
    {
        if ($offset >= $this->nbResults) {
            return [];
        }

        return $this->createNullArray($this->calculateNullArrayLength($offset, $length));
    }

    private function calculateNullArrayLength(int $offset, int $length): int
    {
        $remainCount = $this->remainCount($offset);

        if ($length > $remainCount) {
            return $remainCount;
        }

        return $length;
    }

    private function remainCount(int $offset): int
    {
        return $this->nbResults - $offset;
    }

    private function createNullArray(int $length): array
    {
        return array_fill(0, $length, null);
    }
}
