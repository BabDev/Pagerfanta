<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which generates a null item list based on a number of results.
 *
 * @template T
 * @implements AdapterInterface<T>
 */
class NullAdapter implements AdapterInterface
{
    /**
     * @phpstan-var int<0, max>
     */
    private int $nbResults;

    /**
     * @phpstan-param int<0, max> $nbResults
     */
    public function __construct(int $nbResults = 0)
    {
        $this->nbResults = $nbResults;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    /**
     * The following methods are derived from code of the Zend Framework
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        if ($offset >= $this->nbResults) {
            return [];
        }

        return $this->createNullArray($this->calculateNullArrayLength($offset, $length));
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @phpstan-return int<0, max>
     */
    private function calculateNullArrayLength(int $offset, int $length): int
    {
        $remainCount = $this->remainCount($offset);

        if ($length > $remainCount) {
            return $remainCount;
        }

        return $length;
    }

    /**
     * @phpstan-param int<0, max> $offset
     *
     * @phpstan-return int<0, max>
     */
    private function remainCount(int $offset): int
    {
        return $this->nbResults - $offset;
    }

    /**
     * @phpstan-param int<0, max> $length
     */
    private function createNullArray(int $length): array
    {
        return array_fill(0, $length, null);
    }
}
