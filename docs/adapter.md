# Pagination Adapter

Pagerfanta defines `Pagerfanta\Adapter\AdapterInterface` which is the abstraction layer for any system to provide data to a `Pagerfanta` instance to support pagination lists.

The interface requires two methods to be implemented:

- `getNbResults`: Retrieves a count of the total number of items in the list
    - Generally, an adapter should return a result count of at least 0 as the count will come from either a database result or a `count($foo)` type of operation, however, an adapter can optionally validate the count and throw a `Pagerfanta\Exception\NotValidResultCountException` if the count is a negative number
- `getSlice`: Retrieves the list of items in the current page of the paginated list

```php
<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Exception\NotValidResultCountException;

interface AdapterInterface
{
    /**
     * Returns the number of results for the list.
     * 
     * @throws NotValidResultCountException if the number of results is less than zero.
     */
    public function getNbResults(): int;

    /**
     * Returns an slice of the results representing the current page of items in the list.
     */
    public function getSlice(int $offset, int $length): iterable;
}
```

The `AdapterInterface` is composed of two smaller interfaces, which describe the capabilities of an adapter separately:

- `Pagerfanta\Adapter\CountableAdapterInterface`: An adapter which can count the total number of items in the list with `getNbResults`
- `Pagerfanta\Adapter\OffsetAdapterInterface`: An adapter which can retrieve a page of items with `getSlice`, using an offset and a length

## Cursor Adapters

<div class="docs-note docs-note--new-feature">Cursor adapters were introduced in Pagerfanta 4.10.</div>

Pagerfanta defines `Pagerfanta\Adapter\CursorAdapterInterface` which is the abstraction layer for any system to provide data to a [cursor pager](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination).

The interface requires two methods to be implemented:

- `getSlice`: Retrieves the items following (or preceding, based on the direction of the cursor) the given cursor, along with the cursors pointing to the neighboring pages
    - A null cursor requests the first page
    - The items must always be returned in the sort order of the list, regardless of the direction of the cursor
    - An adapter can throw a `Pagerfanta\Exception\InvalidCursorException` if the cursor is not valid for it, such as when the cursor fields do not match the fields the list is sorted by
- `supportsBackwardNavigation`: Reports whether the adapter can paginate backwards, adapters which cannot should never return a cursor for the previous page

A cursor adapter does not count the total number of items in the list unless it also implements `Pagerfanta\Adapter\CountableAdapterInterface`.

```php
<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Exception\InvalidCursorException;

interface CursorAdapterInterface
{
    /**
     * Returns the slice of results following (or preceding, based on the cursor's direction) the given cursor.
     *
     * @throws InvalidCursorException if the cursor is not valid for this adapter
     */
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice;

    /**
     * Whether this adapter can paginate backwards (i.e. generate a cursor for the previous page).
     */
    public function supportsBackwardNavigation(): bool;
}
```

The `Pagerfanta\Adapter\CursorSlice` returned by `getSlice` holds the items on the page, and the cursors for the previous page (with the `Direction::Previous` direction) and the next page (with the `Direction::Next` direction), which are null when there is no page in that direction.

### Detecting Neighboring Pages

To know whether there is another page without counting the results, an adapter generally fetches one more item than the limit and trims the extra item before returning the slice. The `CursorSlice::fromLookahead()` method implements this approach for adapters which fetch up to `$limit + 1` items in the direction of the cursor (in reverse sort order for a cursor with the previous direction), given a callable which creates the cursor pointing to an item.

```php
<?php

use Pagerfanta\Adapter\CursorAdapterInterface;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;

final class PostCursorAdapter implements CursorAdapterInterface
{
    public function getSlice(?Cursor $cursor, int $limit): CursorSlice
    {
        // Fetch up to $limit + 1 posts after the cursor, or before it in reverse order for a cursor with the previous direction
        $items = $this->fetchPosts($cursor, $limit + 1);

        return CursorSlice::fromLookahead(
            $items,
            $limit,
            $cursor,
            static fn (Post $post, Direction $direction): Cursor => new Cursor(['id' => $post->id], $direction),
        );
    }

    public function supportsBackwardNavigation(): bool
    {
        return true;
    }
}
```
