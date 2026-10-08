<?php declare(strict_types=1);

namespace Pagerfanta\Adapter;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;

/**
 * Adapter which calculates cursor based pagination from a pre-sorted array of items.
 *
 * The key extractor returns the cursor fields for an item, i.e. the values of the fields the array is sorted by. The fields
 * must uniquely identify each item, and a cursor must point to an item in the array.
 *
 * @template T
 *
 * @implements CursorAdapterInterface<T>
 */
class ArrayCursorAdapter implements CursorAdapterInterface, CountableAdapterInterface
{
    /**
     * @var list<T>
     */
    private readonly array $items;

    /**
     * @var callable(T): non-empty-array<string, scalar|null>
     */
    private $keyExtractor;

    /**
     * The position of each item in the list, indexed by its serialized cursor fields.
     *
     * @var array<string, int<0, max>>|null
     */
    private ?array $positions = null;

    /**
     * @param array<T>                                          $array        The items, sorted in the order to paginate them in
     * @param callable(T): non-empty-array<string, scalar|null> $keyExtractor Returns the cursor fields for an item
     */
    public function __construct(array $array, callable $keyExtractor)
    {
        $this->items = array_values($array);
        $this->keyExtractor = $keyExtractor;
    }

    /**
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        return \count($this->items);
    }

    public function supportsBackwardNavigation(): bool
    {
        return true;
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorSlice<T>
     *
     * @throws InvalidCursorException   if the cursor does not point to an item in the array
     * @throws InvalidArgumentException if the key extractor does not return unique, valid cursor fields for every item
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        if (!$cursor instanceof Cursor) {
            return $this->createSlice(0, $limit);
        }

        $position = $this->findPosition($cursor);

        if (Direction::Next === $cursor->direction) {
            return $this->createSlice($position + 1, $limit);
        }

        return $this->createSlice(max(0, $position - $limit), min($limit, $position));
    }

    /**
     * @param int<0, max> $start
     * @param int<0, max> $length
     *
     * @return CursorSlice<T>
     */
    private function createSlice(int $start, int $length): CursorSlice
    {
        $items = \array_slice($this->items, $start, $length);

        if ([] === $items) {
            return new CursorSlice([]);
        }

        $end = $start + \count($items);

        return new CursorSlice(
            $items,
            $start > 0 ? $this->createCursor($items[0], Direction::Previous) : null,
            $end < \count($this->items) ? $this->createCursor($items[array_key_last($items)], Direction::Next) : null,
        );
    }

    /**
     * @param T $item
     *
     * @throws InvalidArgumentException if the key extractor does not return valid cursor fields
     */
    private function createCursor(mixed $item, Direction $direction): Cursor
    {
        $keyExtractor = $this->keyExtractor;

        return new Cursor($keyExtractor($item), $direction);
    }

    /**
     * @return int<0, max>
     *
     * @throws InvalidCursorException if the cursor does not point to an item in the array
     */
    private function findPosition(Cursor $cursor): int
    {
        $this->positions ??= $this->indexPositions();

        return $this->positions[serialize($cursor->fields)] ?? throw new InvalidCursorException('The cursor does not point to an item in the array.');
    }

    /**
     * @return array<string, int<0, max>>
     *
     * @throws InvalidArgumentException if the key extractor does not return unique, valid cursor fields for every item
     */
    private function indexPositions(): array
    {
        $positions = [];

        foreach ($this->items as $position => $item) {
            $key = serialize($this->createCursor($item, Direction::Next)->fields);

            if (isset($positions[$key])) {
                throw new InvalidArgumentException(\sprintf('The cursor fields for the items at positions %d and %d are the same, the key extractor must return unique fields for each item.', $positions[$key], $position));
            }

            $positions[$key] = $position;
        }

        return $positions;
    }
}
