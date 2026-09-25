# Cursor Pagination

<div class="docs-note docs-note--new-feature">Cursor pagination was introduced in Pagerfanta 4.10.</div>

Pagerfanta supports two pagination strategies:

- **Offset pagination** (the `Pagerfanta\Pagerfanta` class) identifies a page by its number, and fetches the items for a page by skipping the items on the pages before it. It always knows the total number of items and pages, so it can link to any page.
- **Cursor pagination** (also known as keyset pagination) identifies a page by a cursor pointing to an item, and fetches the items after (or before) that item. It performs consistently on large lists and is not affected by items being added or removed on earlier pages, but it can only link to the previous and next pages.

## Pager Interfaces

Both strategies share a common set of interfaces, so code such as views and serializers can work with any pager.

| Interface                            | Description                                                                                                               |
|--------------------------------------|---------------------------------------------------------------------------------------------------------------------------|
| `Pagerfanta\PagerInterface`          | The root pager API: the current page results, the max per page, and whether (and where) there are previous and next pages |
| `Pagerfanta\CountablePagerInterface` | A pager which knows the total number of results with `getNbResults()`                                                     |
| `Pagerfanta\OffsetPagerInterface`    | A countable pager using offset pagination, implemented by `Pagerfanta\Pagerfanta`                                         |
| `Pagerfanta\CursorPagerInterface`    | A pager using cursor pagination                                                                                           |

The previous and next pages of any pager are described by a `Pagerfanta\Position\Position`, which is either a `Pagerfanta\Position\PagePosition` (holding a page number) or a `Pagerfanta\Position\CursorPosition` (holding a cursor). Use the `hasPreviousPage()` and `hasNextPage()` methods before calling `getPreviousPosition()` or `getNextPosition()`, which throw a `Pagerfanta\Exception\LogicException` if there is no page in that direction.

```php
<?php

use Pagerfanta\PagerInterface;

function describe(PagerInterface $pager): void
{
    foreach ($pager->getCurrentPageResults() as $item) {
        // ...
    }

    if ($pager->hasNextPage()) {
        $position = $pager->getNextPosition(); // A PagePosition or a CursorPosition
    }
}
```

## Creating A Cursor Pager

Cursor pagers are created with a cursor adapter (see the [available adapters](/open-source/packages/pagerfanta/docs/4.x/adapters)), the maximum number of items per page, and the position of the current page (or null for the first page).

The `Pagerfanta\CursorPagerfantaFactory` creates the right pager for the adapter: a `Pagerfanta\CountableCursorPagerfanta` if the adapter can count its results (it implements `Pagerfanta\Adapter\CountableAdapterInterface`), otherwise a `Pagerfanta\CursorPagerfanta`. Check for `Pagerfanta\CountablePagerInterface` to find out whether the total number of results is available.

```php
<?php

use Pagerfanta\Adapter\ArrayCursorAdapter;
use Pagerfanta\CountablePagerInterface;
use Pagerfanta\CursorPagerfantaFactory;

$adapter = new ArrayCursorAdapter($posts, static fn (array $post): array => ['id' => $post['id']]);

$pager = CursorPagerfantaFactory::create($adapter, 10);

if ($pager instanceof CountablePagerInterface) {
    $pager->getNbResults(); // The total number of posts
}
```

<div class="docs-note">Unlike the <code>Pagerfanta</code> class, the <code>count()</code> method of the cursor pagers returns the number of items on the current page. Use <code>getNbResults()</code> on a countable pager for the total number of results.</div>

Cursor pagers are immutable. To move to another page, create a new pager for its position with the `withPosition()` method, which keeps the adapter and the maximum number of items per page.

```php
<?php

if ($pager->hasNextPage()) {
    $nextPager = $pager->withPosition($pager->getNextPosition());
}
```

### Totals Are Opt-In

Counting the results of a query is often the most expensive part of paginating it, and cursor pagination does not need the total to know whether there is another page. Cursor adapters therefore do not count their results unless they implement `Pagerfanta\Adapter\CountableAdapterInterface`, and a countable pager only counts the results when `getNbResults()` is called.

To add a total to any cursor adapter, decorate it with the `Pagerfanta\Adapter\CountingCursorAdapter`, using either a callable returning the count or another adapter (such as the offset adapter for the same data source) to count with.

```php
<?php

use Pagerfanta\Adapter\CountingCursorAdapter;
use Pagerfanta\Doctrine\DBAL\CursorQueryAdapter;
use Pagerfanta\Doctrine\DBAL\SingleTableQueryAdapter;
use Pagerfanta\Doctrine\DBAL\SortColumn;

$adapter = new CountingCursorAdapter(
    new CursorQueryAdapter($queryBuilder, [new SortColumn('p.id')]),
    new SingleTableQueryAdapter($queryBuilder, 'p.id'),
);
```

### Backward Navigation

Some data sources can only move forward through a cursor (for example, Solr's cursorMark). A cursor pager reports this with its `supportsBackwardNavigation()` method, and a pager which does not support backward navigation never has a previous page. The views omit the previous link entirely for these pagers.

### Auto-Pagination

The cursor pagers support [auto-pagination](/open-source/packages/pagerfanta/docs/4.x/usage#auto-pagination), iterating over the items on every page from the current position onwards. As the pagers are immutable, the pager is not changed by the iteration.

```php
<?php

foreach ($pager->autoPagingIterator() as $item) {
    // Iterate over each item from all pages of the result set
}
```

## Cursors

A `Pagerfanta\Cursor\Cursor` holds the sort key values of the item to paginate from, keyed by the sort key (such as `['p.createdAt' => '2026-09-25 12:00:00', 'p.id' => 42]`), and the `Pagerfanta\Cursor\Direction` to paginate in (`Direction::Next` or `Direction::Previous`). The values must be scalars or null.

### Encoding Cursors

Cursors are converted to and from strings, such as for a query string parameter in a URL, with a `Pagerfanta\Cursor\CursorEncoderInterface`. The library provides the `Pagerfanta\Cursor\Base64JsonCursorEncoder`, which encodes cursors as URL-safe Base64 JSON strings.

```php
<?php

use Pagerfanta\Cursor\Base64JsonCursorEncoder;
use Pagerfanta\Exception\InvalidCursorException;
use Pagerfanta\Position\CursorPosition;

$encoder = new Base64JsonCursorEncoder();

// Encode the cursor for the next page into a URL
$url = '/posts?cursor=' . $encoder->encode($pager->getNextPosition()->cursor);

// Decode the cursor from the request
try {
    $position = isset($_GET['cursor']) ? new CursorPosition($encoder->decode($_GET['cursor'])) : null;
} catch (InvalidCursorException) {
    // Respond with a 400 Bad Request error
}
```

<div class="docs-note">The <code>Base64JsonCursorEncoder</code> does not sign the cursors it encodes, so a client can decode and alter a cursor to paginate from any position allowed by the underlying query. The adapters validate that a cursor matches their sort fields before using it, but if your application needs to prevent tampering, decorate the encoder with one which signs the payload.</div>

## Rendering Cursor Pagers

Cursor pagers can only link to the previous and next pages, so they are rendered with a sequential view instead of a view with numbered page links. See the [views documentation](/open-source/packages/pagerfanta/docs/4.x/views#sequential-views) for details, and the [route generator documentation](/open-source/packages/pagerfanta/docs/4.x/route-generator#position-route-generators) for generating the URLs for cursors.
