<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which transforms the result of other adapter.
 *
 * @template T
 * @template Transformed
 * @implements AdapterInterface<Transformed>
 */
class TransformingAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface<T>
     */
    private $adapter;

    /**
     * @var callable
     * @phpstan-var callable(T, array-key): Transformed
     */
    private $transformer;

    /**
     * @phpstan-param AdapterInterface<T>                 $adapter
     * @phpstan-param callable(T, array-key): Transformed $transformer
     */
    public function __construct(AdapterInterface $adapter, callable $transformer)
    {
        $this->adapter = $adapter;
        $this->transformer = $transformer;
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->adapter->getNbResults();
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return iterable<array-key, Transformed>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $transformer = $this->transformer;

        foreach ($this->adapter->getSlice($offset, $length) as $key => $item) {
            yield $transformer($item, $key);
        }
    }
}
