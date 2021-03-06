<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

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
