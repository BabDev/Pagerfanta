<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * Adapter which calculates pagination from callable functions.
 */
class CallbackAdapter implements AdapterInterface
{
    /**
     * @var callable
     * @phpstan-var callable(): int
     */
    private $nbResultsCallable;

    /**
     * @var callable
     * @phpstan-var callable(int $offset, int $length): iterable
     */
    private $sliceCallable;

    /**
     * @phpstan-param callable(): int                              $nbResultsCallable
     * @phpstan-param callable(int $offset, int $length): iterable $sliceCallable
     *
     * @throws InvalidArgumentException if a non-callable was passed as either constructor argument
     */
    public function __construct($nbResultsCallable, $sliceCallable)
    {
        if (!\is_callable($nbResultsCallable)) {
            throw new InvalidArgumentException(sprintf('The $nbResultsCallable argument of the %s constructor must be a callable, %s given.', self::class, get_debug_type($nbResultsCallable)));
        }

        if (!\is_callable($sliceCallable)) {
            throw new InvalidArgumentException(sprintf('The $sliceCallable argument of the %s constructor must be a callable, %s given.', self::class, get_debug_type($sliceCallable)));
        }

        $this->nbResultsCallable = $nbResultsCallable;
        $this->sliceCallable = $sliceCallable;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        $callable = $this->nbResultsCallable;

        return $callable();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice($offset, $length)
    {
        $callable = $this->sliceCallable;

        return $callable($offset, $length);
    }
}
