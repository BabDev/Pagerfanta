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
