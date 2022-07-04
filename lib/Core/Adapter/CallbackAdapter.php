<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which calculates pagination from callable functions.
 *
 * @template T
 * @implements AdapterInterface<T>
 */
class CallbackAdapter implements AdapterInterface
{
    /**
     * @var callable
     * @phpstan-var callable(): int<0, max>
     */
    private $nbResultsCallable;

    /**
     * @var callable
     * @phpstan-var callable(int<0, max> $offset, int<0, max> $length): iterable<array-key, T>
     */
    private $sliceCallable;

    /**
     * @phpstan-param callable(): int<0, max>                                                    $nbResultsCallable
     * @phpstan-param callable(int<0, max> $offset, int<0, max> $length): iterable<array-key, T> $sliceCallable
     */
    public function __construct(callable $nbResultsCallable, callable $sliceCallable)
    {
        $this->nbResultsCallable = $nbResultsCallable;
        $this->sliceCallable = $sliceCallable;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        $callable = $this->nbResultsCallable;

        return $callable();
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $callable = $this->sliceCallable;

        return $callable($offset, $length);
    }
}
