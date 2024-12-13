<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

/**
 * Adapter which transforms the result of other adapter.
 *
 * @template T
 *
 * @template-covariant Transformed
 *
 * @implements AdapterInterface<Transformed>
 */
class TransformingAdapter implements AdapterInterface
{
    /**
     * @var callable(T, array-key): Transformed
     */
    private $transformer;

    /**
     * @param AdapterInterface<T>                 $adapter
     * @param callable(T, array-key): Transformed $transformer
     */
    public function __construct(
        private readonly AdapterInterface $adapter,
        callable $transformer,
    ) {
        $this->transformer = $transformer;
    }

    /**
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->adapter->getNbResults();
    }

    /**
     * @param int<0, max> $offset
     * @param int<0, max> $length
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
