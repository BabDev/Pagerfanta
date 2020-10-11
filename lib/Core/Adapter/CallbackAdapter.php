<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

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
     */
    public function __construct(callable $nbResultsCallable, callable $sliceCallable)
    {
        $this->nbResultsCallable = $nbResultsCallable;
        $this->sliceCallable = $sliceCallable;
    }

    public function getNbResults(): int
    {
        $callable = $this->nbResultsCallable;

        return $callable();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        $callable = $this->sliceCallable;

        return $callable($offset, $length);
    }
}
