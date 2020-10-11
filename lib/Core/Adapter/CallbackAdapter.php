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
     */
    private $nbResultsCallable;

    /**
     * @var callable
     */
    private $sliceCallable;

    /**
     * @param callable $nbResultsCallable a callable to retrieve the number of results in the lookup, the callable should have a signature of `function (): int {}`
     * @param callable $sliceCallable     a callable to retrieve the results for the current page, the callable should have a signature of `function (int $offset, int $length): iterable {}`
     *
     * @throws InvalidArgumentException if a non-callable was passed as either constructor argument
     */
    public function __construct($nbResultsCallable, $sliceCallable)
    {
        if (!\is_callable($nbResultsCallable)) {
            throw new InvalidArgumentException(sprintf('The $nbResultsCallable argument of the %s constructor must be a callable, a %s was given.', self::class, \gettype($nbResultsCallable)));
        }

        if (!\is_callable($sliceCallable)) {
            throw new InvalidArgumentException(sprintf('The $sliceCallable argument of the %s constructor must be a callable, a %s was given.', self::class, \gettype($sliceCallable)));
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
