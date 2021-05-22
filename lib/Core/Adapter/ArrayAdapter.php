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
     *
     * @deprecated to be removed in 4.0
     */
    public function getArray(): array
    {
        trigger_deprecation('pagerfanta/pagerfanta', '3.2', 'Retrieving the injected array from "%s" is deprecated and will be removed in 4.0.', static::class);

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
