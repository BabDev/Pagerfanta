# Pagination Adapter

Pagerfanta defines `Pagerfanta\Adapter\AdapterInterface` which is the abstraction layer for any system to provide data to a `Pagerfanta` instance to support pagination lists.

The interface requires two methods to be implemented:

- `getNbResults`: Retrieves a count of the total number of items in the list
- `getSlice`: Retrieves the list of items in the current page of the paginated list

```php
<?php

namespace Pagerfanta\Adapter;

interface AdapterInterface
{
    /**
     * Returns the number of results for the list.
     */
    public function getNbResults(): int;

    /**
     * Returns an slice of the results representing the current page of items in the list.
     */
    public function getSlice(int $offset, int $length): iterable;
}
```
