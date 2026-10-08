<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Cursor as DoctrineCursor;
use Doctrine\ORM\Tools\Pagination\CursorPage;
use Doctrine\ORM\Tools\Pagination\CursorPaginator;
use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\LogicException;

/**
 * Adapter which calculates cursor based pagination from a Doctrine ORM Query or QueryBuilder.
 *
 * This adapter uses the cursor paginator from `doctrine/orm` 3.7 or later. The query's ORDER BY clause must be deterministic
 * (i.e. end with a unique field such as the identifier) and every item in it must be a field of an entity. The cursor fields
 * are keyed by the DQL path of each ORDER BY item (e.g. "p.createdAt").
 *
 * @template T
 *
 * @implements CursorAdapterInterface<T>
 */
class CursorQueryAdapter implements CursorAdapterInterface
{
    /**
     * @var CursorPage<T>|null
     */
    private ?CursorPage $page = null;

    /**
     * @param bool      $fetchJoinCollection Whether the query joins a collection (true by default)
     * @param bool|null $useOutputWalkers    Flag indicating whether output walkers are used in the paginator
     *
     * @throws LogicException if the installed `doctrine/orm` version does not support cursor pagination
     */
    public function __construct(
        private readonly Query|QueryBuilder $query,
        private readonly bool $fetchJoinCollection = true,
        private readonly ?bool $useOutputWalkers = null,
    ) {
        if (!class_exists(CursorPaginator::class)) {
            throw new LogicException(\sprintf('The "%s" class requires doctrine/orm 3.7 or later.', static::class));
        }
    }

    public function supportsBackwardNavigation(): bool
    {
        return true;
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorSlice<T>
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        $this->page = $this->createPaginator($limit)->paginate($this->query, $cursor instanceof Cursor ? $this->toDoctrineCursor($cursor) : null);

        return new CursorSlice(
            $this->page->getItems(),
            $this->page->hasPreviousPage() && [] !== $this->page->getItems() ? $this->fromDoctrineCursor($this->page->getPreviousCursor()) : null,
            $this->page->hasNextPage() && [] !== $this->page->getItems() ? $this->fromDoctrineCursor($this->page->getNextCursor()) : null,
        );
    }

    /**
     * Counts the total number of results, reusing the page from the last slice when possible.
     *
     * @return int<0, max>
     */
    protected function countResults(): int
    {
        $this->page ??= $this->createPaginator(1)->paginate($this->query);

        return max(0, $this->page->getTotalCount());
    }

    /**
     * @param positive-int $limit
     *
     * @return CursorPaginator<T>
     */
    private function createPaginator(int $limit): CursorPaginator
    {
        return new CursorPaginator($limit, $this->fetchJoinCollection, $this->useOutputWalkers);
    }

    private function toDoctrineCursor(Cursor $cursor): DoctrineCursor
    {
        // The ORM documents cursor values as scalars but accepts null at runtime (i.e. when decoding its own cursors), so null values are passed through as-is and their handling is left to the ORM.
        // @phpstan-ignore-next-line argument.type
        return new DoctrineCursor($cursor->fields, Direction::Next === $cursor->direction);
    }

    /**
     * @throws LogicException if the cursor has no fields
     */
    private function fromDoctrineCursor(DoctrineCursor $cursor): Cursor
    {
        $fields = $cursor->toArray();
        unset($fields['_isNext']);

        if ([] === $fields) {
            throw new LogicException('A cursor could not be created for the query, ensure its ORDER BY clause contains at least one entity field.');
        }

        return new Cursor($fields, $cursor->isNext() ? Direction::Next : Direction::Previous);
    }
}
