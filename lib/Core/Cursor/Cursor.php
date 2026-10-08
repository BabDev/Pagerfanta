<?php declare(strict_types=1);

namespace Pagerfanta\Cursor;

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * A cursor holds the sort key values of the item to paginate from and the direction to paginate in.
 */
final class Cursor
{
    /**
     * @param non-empty-array<string, scalar|null> $fields The sort key values, keyed by sort key (e.g. "p.createdAt" or "_id")
     *
     * @throws InvalidArgumentException if the fields are empty, not keyed by string, or contain non-scalar values
     */
    public function __construct(
        public readonly array $fields,
        public readonly Direction $direction = Direction::Next,
    ) {
        if ([] === $fields) {
            throw new InvalidArgumentException('A cursor must have at least one field.');
        }

        foreach ($fields as $key => $value) {
            if (!\is_string($key)) {
                throw new InvalidArgumentException(\sprintf('The fields of a cursor must be keyed by their sort key, "%s" given as a key.', get_debug_type($key)));
            }

            if (null !== $value && !\is_scalar($value)) {
                throw new InvalidArgumentException(\sprintf('The value of the "%s" cursor field must be a scalar or null, "%s" given.', $key, get_debug_type($value)));
            }
        }
    }
}
