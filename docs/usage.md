# Usage

In its basic use, you will set up an adapter which contains the data to be paginated, instantiate a new `Pagerfanta` instance, configure your pagination data (current page, number of items per page, etc.) and render the result.

```php
<?php

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

$adapter = new ArrayAdapter([]);
$pagerfanta = new Pagerfanta($adapter);

// By default, this will return up to 10 items for the first page of results
$currentPageResults = $pagerfanta->getCurrentPageResults();
```

When configuring the `Pagerfanta` instance, you must set the maximum number of items per page before setting the current page. This is required due to the internal validation made by the `Pagerfanta` class to prevent invalid pagination state as the allowed current page is based on the number of items in the pagination list (provided by the adapter) and the maximum number of items per page. To simplify this part of setting the configuration, a static constructor is provided that will perform these steps in the right order.

<div class="docs-note docs-note--new-feature">The static constructor was introduced in Pagerfanta 3.4.</div>

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$adapter = new NullAdapter(100);
$pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, 2, 5);

// This will the return 5 items from the second page of the paginated list
$currentPageResults = $pagerfanta->getCurrentPageResults();
```

## Managing The Items Per Page

You can set the number of items that should be shown on a page using the `setMaxPerPage` method on the `Pagerfanta` instance, and get the current value using the `getMaxPerPage` method.

You should set this before fetching the current page results otherwise you will get the default data (10 items for the first page).

The `setMaxPerPage` method will throw a `Pagerfanta\Exception\LessThan1MaxPerPageException` if you attempt to set this to zero or a negative number.

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$pagerfanta = new Pagerfanta(new NullAdapter(10));

$pagerfanta->setMaxPerPage(3);
$pagerfanta->getMaxPerPage(); // Will return 3
```

## Managing The Current Page

You can set the current page for the list using the `setCurrentPage` method on the `Pagerfanta` instance, and get the current value using the `getCurrentPage` method.

You should set this before fetching the current page results otherwise you will get the default data (10 items for the first page). Typically, you will set this based on information from the request (i.e. a `page` GET parameter).

The `setCurrentPage` method will throw a `Pagerfanta\Exception\LessThan1CurrentPageException` if you attempt to set this to zero or a negative number, or a `Pagerfanta\Exception\OutOfRangeCurrentPageException` if you attempt to set this to a page number that is outside the allowed value (calculated based on the number of items in the list and the number of items to be shown per page).

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$pagerfanta = new Pagerfanta(new NullAdapter(30));

$pagerfanta->setCurrentPage(3);
$pagerfanta->getCurrentPage(); // Will return 3
```

## Determining If Pagination Is Necessary

You can check if the list needs to be paginated using the `haveToPaginate` method on the `Pagerfanta` instance.

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$pagerfanta = new Pagerfanta(new NullAdapter(30));

$pagerfanta->setMaxPerPage(10);
$pagerfanta->haveToPaginate(); // Will return true since there are more items than the max per page

$pagerfanta->setMaxPerPage(50);
$pagerfanta->haveToPaginate(); // Will return false since there are less items than the max per page
```

## Get Number Of Pages In List

You can get the number of pages for your list using the `getNbPages` method on the `Pagerfanta` instance.

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$pagerfanta = new Pagerfanta(new NullAdapter(30));

$pagerfanta->setMaxPerPage(10);
$pagerfanta->getNbPages(); // Will return 3

$pagerfanta->setMaxPerPage(50);
$pagerfanta->getNbPages(); // Will return 1
```

## Set and Reset Maximum Number Of Pages In List

You can set the maximum number of pages for your list using the `setMaxNbPages` method on the `Pagerfanta` instance.

You can reset the maximum number of pages to the number of pages determined by the pagination settings using the `resetMaxNbPages` method on the `Pagerfanta` instance.

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$pagerfanta = new Pagerfanta(new NullAdapter(30));

$pagerfanta->setMaxNbPages(2);
$pagerfanta->getNbPages(); // Will return 2

$pagerfanta->setMaxNbPages(5);
$pagerfanta->getNbPages(); // Will return 3 since the configured max is less than the number of pages

$pagerfanta->resetMaxNbPages();
$pagerfanta->getNbPages(); // Will return 3 since there is no configured max
```

## Previous/Next Page Helpers

You can check if the list has a previous or next page using the `hasPreviousPage` and `hasNextMethods` methods respectively on the `Pagerfanta` instance.

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$pagerfanta = new Pagerfanta(new NullAdapter(30));

$pagerfanta->hasPreviousPage(); // Will return false
$pagerfanta->hasNextPage(); // Will return true
```

You can get the previous and next page numbers using the `getPreviousPage` and `getNextMethods` methods respectively on the `Pagerfanta` instance.

A `Pagerfanta\Exception\LogicException` will be thrown if there is not a page in the requested direction

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$pagerfanta = new Pagerfanta(new NullAdapter(30));

if ($pagerfanta->hasPreviousPage()) {
    $pagerfanta->getPreviousPage(); // Will not be executed
}

if ($pagerfanta->hasNextPage()) {
    $pagerfanta->getNextPage(); // Will return 2
}
```

## Retrieving The Adapter

<div class="docs-note docs-note--deprecated-feature">Accessing the adapter from a `Pagerfanta` instance is deprecated as of Pagerfanta 3.2 and will be removed in 4.0.</div>

If needed, you can retrieve the underlying adapter using the `getAdapter` method on the `Pagerfanta` instance.

```php
<?php

use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;

$pagerfanta = new Pagerfanta(new NullAdapter(30));

$pagerfanta->getAdapter(); // Will return the NullAdapter instance given
```
