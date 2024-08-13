<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Exception\NotValidResultCountException;

/**
 * Adapter which calculates pagination from callable functions.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class CallbackAdapter implements AdapterInterface
{
    /**
     * @var callable
     *
     * @phpstan-var callable(): int<0, max>
     */
    private $nbResultsCallable;

    /**
     * @var callable
     *
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
     *
     * @throws NotValidResultCountException if the number of results is less than zero
     */
    public function getNbResults(): int
    {
        $callable = $this->nbResultsCallable;

        $count = $callable();

        if ($count < 0) {
            throw new NotValidResultCountException(\sprintf('The callable to calculate the number of results in "%s()" must return a number greater than or equal to zero.', __METHOD__));
        }

        return $count;
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
