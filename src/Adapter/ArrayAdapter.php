<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which calculates pagination from an array of items.
 */
class ArrayAdapter implements AdapterInterface
{
    private array $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Retrieves the array of items.
     */
    public function getArray(): array
    {
        return $this->array;
    }

    public function getNbResults(): int
    {
        return \count($this->array);
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return \array_slice($this->array, $offset, $length);
    }
}
