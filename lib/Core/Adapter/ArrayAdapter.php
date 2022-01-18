<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which calculates pagination from an array of items.
 *
 * @template T
 * @implements AdapterInterface<T>
 */
class ArrayAdapter implements AdapterInterface
{
    /**
     * @var array<T>
     */
    private array $array;

    /**
     * @param array<T> $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Retrieves the array of items.
     *
     * @return array<T>
     *
     * @deprecated to be removed in 4.0
     */
    public function getArray(): array
    {
        trigger_deprecation('pagerfanta/pagerfanta', '3.2', 'Retrieving the injected array from "%s" is deprecated and will be removed in 4.0.', static::class);

        return $this->array;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return \count($this->array);
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return \array_slice($this->array, $offset, $length);
    }
}
