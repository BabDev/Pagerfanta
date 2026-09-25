# Available Adapters

The Pagerfanta package provides out-of-the-box support for a number of storage backends. Please review the [installation guide](/open-source/packages/pagerfanta/docs/4.x/intro) for details on how to install optional packages.

## Third Party

Adapters are provided for a number of third party storage solutions, allowing this package to be used in a variety of environments.

### Doctrine

Adapters are available for a number of [Doctrine](https://www.doctrine-project.org/) packages.

#### Collections

The collections adapters are available with the `pagerfanta/doctrine-collections-adapter` package for use with [Doctrine Collections](https://www.doctrine-project.org/projects/collections.html).

Below is an example of using the `CollectionAdapter` on a collection from an entity.

```php
<?php

use App\Entity\User;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;

$config = new Configuration();

$connection = DriverManager::getConnection(
    [
        'driver' => 'pdo_sqlite',
        'memory' => true,
    ],
    $config
);

$em = new EntityManager($connection, $config);

$user = $em->find(User::class, 1);

$adapter = new CollectionAdapter($user->getGroups());
```

Below is an example of using the `SelectableAdapter` on a class which implements `Doctrine\Common\Collection\Selectable` (such as a `Doctrine\ORM\PersistentCollection`).

```php
<?php

use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Doctrine\Collections\SelectableAdapter;

$config = new Configuration();

$connection = DriverManager::getConnection(
    [
        'driver' => 'pdo_sqlite',
        'memory' => true,
    ],
    $config
);

$em = new EntityManager($connection, $config);

$user = $em->find(User::class, 1);

$criteria = Criteria::create()->andWhere(Criteria::expr()->in('id', [1, 2, 3]));

$adapter = new SelectableAdapter($user->getGroups(), $criteria);
```

##### Cursor Pagination

<div class="docs-note docs-note--new-feature">The <code>SelectableCursorAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `SelectableCursorAdapter` supports [cursor pagination](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination) on a class which implements `Doctrine\Common\Collection\Selectable`.

The class constructor requires the `Selectable` instance, a `Doctrine\Common\Collections\Criteria` instance, and the fields to sort the items by, in order of precedence, mapped to their sort order (either `'ASC'`/`'DESC'`, a `Doctrine\Common\Collections\Order` case, or a `\SortDirection` case). The sort fields replace any orderings on the criteria, and together they must uniquely identify each item (i.e. the last field should be the identifier). The sort fields must have scalar, non-null values.

```php
<?php

use Doctrine\Common\Collections\Criteria;
use Pagerfanta\Doctrine\Collections\SelectableCursorAdapter;

$criteria = Criteria::create()->andWhere(Criteria::expr()->eq('active', true));

$adapter = new SelectableCursorAdapter($user->getGroups(), $criteria, ['name' => 'ASC', 'id' => 'ASC']);
```

To count the results, decorate the adapter with a [`CountingCursorAdapter`](#counting) using a `SelectableAdapter` for the same criteria.

#### DBAL

The DBAL adapters are available with the `pagerfanta/doctrine-dbal-adapter` package for use with [Doctrine's DBAL](https://www.doctrine-project.org/projects/dbal.html).

The `SingleTableQueryAdapter` is a helper class which is optimized for queries which do not have any join statements.

The class constructor requires a `Doctrine\DBAL\Query\QueryBuilder` and the field name that should be counted (typically this will be your primary key).

<div class="docs-note">Using this adapter requires that you have a table alias for your query.</div>

Below is an example of using the `SingleTableQueryAdapter`.

```php
<?php

use Doctrine\DBAL\DriverManager;
use Pagerfanta\Doctrine\DBAL\SingleTableQueryAdapter;

$params = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];

$connection = DriverManager::getConnection($params);

$query = $connection->createQueryBuilder()
    ->select('p.*')
    ->from('posts', 'p');

$adapter = new SingleTableQueryAdapter($query, 'p.id');
```

The `QueryAdapter` is the main adapter for use with the DBAL package, you should use this on queries that have join statements.

The class constructor requires a `Doctrine\DBAL\Query\QueryBuilder` and a callable which can be used to modify a clone of the query builder for a COUNT query. The callable should have a signature of `function (QueryBuilder $queryBuilder): QueryBuilder {}`.

<div class="docs-note docs-note--deprecated-feature">Before Pagerfanta 4.6, a return from the callable was ignored and the query builder passed to the callable was returned. This approach is deprecated in favor of the callable returning a query builder object, be it the provided builder or a new instance. In Pagerfanta 5.0, this return will be required.</div>

Below is an example of using the `QueryAdapter`.

```php
<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;

$params = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];

$connection = DriverManager::getConnection($params);

$query = $connection->createQueryBuilder()
    ->select('p.*')
    ->from('posts', 'p');

$countQueryBuilderModifier = static function (QueryBuilder $queryBuilder): QueryBuilder {
    $queryBuilder->select('COUNT(DISTINCT p.id) AS total_results')
        ->setMaxResults(1);

    return $queryBuilder;
};

$adapter = new QueryAdapter($query, $countQueryBuilderModifier);
```

##### Cursor Pagination

<div class="docs-note docs-note--new-feature">The DBAL <code>CursorQueryAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `CursorQueryAdapter` supports [cursor pagination](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination) (also known as keyset pagination) on a `Doctrine\DBAL\Query\QueryBuilder`.

As the query builder cannot be inspected for its ORDER BY clause, the class constructor requires a list of `Pagerfanta\Doctrine\DBAL\SortColumn` instances describing the columns to sort the query by, in order of precedence. The adapter replaces any ORDER BY clause on the query with these columns, and together they must uniquely identify each row (i.e. the last column should be the primary key). The sort columns must be selected by the query and must not contain null values.

Each sort column takes the SQL expression for the column (including the table alias), the sort order (`'ASC'` or `'DESC'`), and optionally the key of the column in the result rows (which defaults to the part of the expression after the last `.`).

<div class="docs-note">The sort column expressions are added to the query as-is, never build them from user input.</div>

```php
<?php

use Pagerfanta\Doctrine\DBAL\CursorQueryAdapter;
use Pagerfanta\Doctrine\DBAL\SortColumn;

$query = $connection->createQueryBuilder()
    ->select('p.*')
    ->from('posts', 'p');

$adapter = new CursorQueryAdapter(
    $query,
    [
        new SortColumn('p.published_at', 'DESC'),
        new SortColumn('p.id', 'DESC'),
    ]
);
```

The adapter binds the cursor values as parameters named `pagerfanta_cursor_0`, `pagerfanta_cursor_1`, and so on, so the query must not use parameters with these names.

To count the results, decorate the adapter with a [`CountingCursorAdapter`](#counting) using a `QueryAdapter` or `SingleTableQueryAdapter` for the same query.

#### MongoDB ODM

The MongoDB ODM adapter is available with the `pagerfanta/doctrine-mongodb-odm-adapter` package for use with [Doctrine' MongoDB ODM](https://www.doctrine-project.org/projects/mongodb-odm.html).

The class constructor requires a `Doctrine\ODM\MongoDB\Query\Builder`.

Below is an example of using the `QueryAdapter`.

```php
<?php

use App\Document\Article;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pagerfanta\Doctrine\MongoDBODM\QueryAdapter;

$config = new Configuration();

$dm = DocumentManager::create(null, $config);

$query = $dm->createQueryBuilder(Article::class);

$adapter = new QueryAdapter($query);
```

<div class="docs-note">Cursor pagination is not yet available for the MongoDB ODM, support is planned should the paginators proposed in <a href="https://github.com/doctrine/mongodb-odm/pull/2992" target="_blank" rel="noopener nofollow">doctrine/mongodb-odm#2992</a> be released.</div>

#### ORM

The ORM adapter is available with the `pagerfanta/doctrine-orm-adapter` package for use with [Doctrine's ORM](https://www.doctrine-project.org/projects/orm.html).

The class constructor requires either a `Doctrine\ORM\Query` or `Doctrine\ORM\QueryBuilder` instance. You can also specify whether to query join collections or use output walkers on the underlying [`Paginator`](https://www.doctrine-project.org/projects/doctrine-orm/en/current/tutorials/pagination.html#pagination).

Below is an example of using the `QueryAdapter`.

```php
<?php

use App\Entity\User;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

$config = new Configuration();

$connection = DriverManager::getConnection(
    [
        'driver' => 'pdo_sqlite',
        'memory' => true,
    ],
    $config
);

$em = new EntityManager($connection, $config);

$repository = $em->getRepository(User::class);

$query = $repository->createQueryBuilder('u');

$adapter = new QueryAdapter($query);
```

##### Cursor Pagination

<div class="docs-note docs-note--new-feature">The ORM <code>CursorQueryAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `CursorQueryAdapter` supports [cursor pagination](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination) using the [cursor paginator](https://www.doctrine-project.org/projects/doctrine-orm/en/current/tutorials/pagination.html) from Doctrine ORM 3.7 or later.

The class constructor requires either a `Doctrine\ORM\Query` or `Doctrine\ORM\QueryBuilder` instance, and accepts the same options as the `QueryAdapter`. The query's ORDER BY clause must be deterministic (i.e. end with a unique field such as the identifier), and every item in it must be a field of an entity. The cursor fields are keyed by the DQL path of each ORDER BY item (such as `p.createdAt`).

```php
<?php

use Pagerfanta\Doctrine\ORM\CursorQueryAdapter;

$query = $repository->createQueryBuilder('p')
    ->orderBy('p.createdAt', 'DESC')
    ->addOrderBy('p.id', 'DESC');

$adapter = new CursorQueryAdapter($query);
```

Use the `CountableCursorQueryAdapter` if the total number of results is needed, which runs an extra COUNT query when the total is requested.

#### PHPCR ODM

The PHPCR ODM adapter is available with the `pagerfanta/doctrine-phpcr-odm-adapter` package for use with [Doctrine's PHPCR ODM](https://www.doctrine-project.org/projects/phpcr-odm.html).

The class constructor requires a `Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder`.

Below is an example of using the `QueryAdapter`.

```php
<?php

use App\Document\Article;
use Doctrine\ODM\PHPCR\Configuration;
use Doctrine\ODM\PHPCR\DocumentManager;
use Pagerfanta\Doctrine\PHPCRODM\QueryAdapter;

$config = new Configuration();

$dm = DocumentManager::create($session, $config);

$query = $dm->createQueryBuilder()
    ->from(Article::class);

$adapter = new QueryAdapter($query);
```

### Elastica

The Elastica adapter is available with the `pagerfanta/elastica-adapter` package for use with [Elastica](https://elastica.io/).

```php
<?php

use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Term;
use Pagerfanta\Elastica\ElasticaAdapter;

// Searchable can be any valid searchable Elastica object. For example, a Type or Index
$searchable = new Index($elasticaClient, 'index_name');

// A Query can be any valid Elastica query (json, array, Query object)
$query = Query::create(
    new Term(
        [
            'name' => 'Fred',
        ]
    )
);

$adapter = new ElasticaAdapter($searchable, $query);
```

<div class="docs-note">Be careful when paginating a huge set of documents. By default, offset + limit cannot exceed 10,000 items. You can mitigate this by setting the <code>$maxResults</code> parameter when constructing the <code>ElasticaAdapter</code>. For more information, see <a href="https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892" target="_blank" rel="noopener nofollow">https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892</a>.</div>

#### Cursor Pagination

<div class="docs-note docs-note--new-feature">The <code>ElasticaCursorAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `ElasticaCursorAdapter` supports [cursor pagination](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination) using Elasticsearch's `search_after` parameter, which is not limited by the maximum result window.

The class constructor requires the searchable object, the query, and the fields to sort the documents by, in order of precedence, mapped to their sort order (`'asc'` or `'desc'`) or to their sort options (which must include the `order`). The sort fields replace any sort on the query, and together they must uniquely identify each document (i.e. the last field should be a unique tiebreaker field). Search options can be given as the last argument.

```php
<?php

use Elastica\Query;
use Pagerfanta\Elastica\ElasticaCursorAdapter;

$adapter = new ElasticaCursorAdapter(
    $searchable,
    new Query(),
    [
        'published_at' => 'desc',
        'score' => ['order' => 'asc', 'missing' => '_first'],
        'post_id' => 'asc',
    ]
);
```

Backward navigation is supported by searching with the reversed sort. When reversing the sort, a field's `missing` option is also reversed so documents without a value keep their position relative to the other documents.

<div class="docs-note">Without a point in time, the pages may shift if the index changes while paginating. Elasticsearch also discourages sorting on the <code>_id</code> field, so use a unique field stored in the document as the tiebreaker.</div>

To count the results, decorate the adapter with a [`CountingCursorAdapter`](#counting) using an `ElasticaAdapter` for the same query.

### Solarium

The Solarium adapter is available with the `pagerfanta/solarium-adapter` package for use with [Solarium](https://github.com/solariumphp/solarium).

```php
<?php

use Pagerfanta\Solarium\SolariumAdapter;

$query = $solarium->createSelect();
$query->setQuery('search term');

$adapter = new SolariumAdapter($solarium, $query);
```

#### Cursor Pagination

<div class="docs-note docs-note--new-feature">The <code>SolariumCursorAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `SolariumCursorAdapter` supports [cursor pagination](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination) using Solr's `cursorMark`. The query's sort must include the collection's `uniqueKey` field.

```php
<?php

use Pagerfanta\Solarium\SolariumCursorAdapter;

$query = $solarium->createSelect();
$query->setQuery('search term');
$query->addSort('published_at', $query::SORT_DESC);
$query->addSort('id', $query::SORT_ASC);

$adapter = new SolariumCursorAdapter($solarium, $query);
```

Solr cursors can only move forward, so this adapter does not support backward navigation.

To count the results, decorate the adapter with a [`CountingCursorAdapter`](#counting) using a `SolariumAdapter` for the same query.

## First Party

There are also several "first party" adapters which are not dependent upon an external storage solution. All first party adapters are available with the `pagerfanta/core` package.

### Array

The `ArrayAdapter` is used to paginate an array of items.

```php
<?php

use Pagerfanta\Adapter\ArrayAdapter;

$adapter = new ArrayAdapter([]);
```

#### Cursor Pagination

<div class="docs-note docs-note--new-feature">The <code>ArrayCursorAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `ArrayCursorAdapter` is used for [cursor pagination](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination) of a pre-sorted array of items. It takes the array and a callable which returns the cursor fields for an item (the values of the fields the array is sorted by), with a signature of `function (mixed $item): array {}`. The fields must uniquely identify each item.

```php
<?php

use Pagerfanta\Adapter\ArrayCursorAdapter;

$adapter = new ArrayCursorAdapter($posts, static fn (array $post): array => ['id' => $post['id']]);
```

### Callback

The `CallbackAdapter` uses callable functions to process pagination.

The adapter takes two callables:

- `$nbResultsCallable`: A callable to count the number items in the list, the callable should have a signature of `function (): int {}`
- `$sliceCallable`: A callable to get the items for the current page in the paginated list, the callable should have a signature of `function (int $offset, int $length): iterable {}`

```php
<?php

use Pagerfanta\Adapter\CallbackAdapter;

$adapter = new CallbackAdapter(
    static fn (): int => 0,
    static fn (int $offset, int $length): iterable => []
);
```

#### Cursor Pagination

<div class="docs-note docs-note--new-feature">The <code>CallbackCursorAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `CallbackCursorAdapter` uses a callable to process [cursor pagination](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination). The callable should have a signature of `function (?Cursor $cursor, int $limit): CursorSlice {}` and follow the [cursor adapter contract](/open-source/packages/pagerfanta/docs/4.x/adapter#cursor-adapters). Backward navigation is not supported unless enabled with the second argument.

```php
<?php

use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;

$adapter = new CallbackCursorAdapter(
    static fn (?Cursor $cursor, int $limit): CursorSlice => new CursorSlice([]),
    supportsBackwardNavigation: true,
);
```

### Counting

<div class="docs-note docs-note--new-feature">The <code>CountingCursorAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `CountingCursorAdapter` is a cursor adapter decorator which adds the total number of results to a cursor adapter, using either a callable with a signature of `function (): int {}` or an adapter implementing `Pagerfanta\Adapter\CountableAdapterInterface` (such as the offset adapter for the same data source).

```php
<?php

use Pagerfanta\Adapter\ArrayCursorAdapter;
use Pagerfanta\Adapter\CountingCursorAdapter;
use Pagerfanta\Adapter\TransformingCursorAdapter;

$adapter = new CountingCursorAdapter(
    new TransformingCursorAdapter(
        new ArrayCursorAdapter($posts, static fn (array $post): array => ['id' => $post['id']]),
        static fn (array $post, int $key): string => $post['title']
    ),
    static fn (): int => \count($posts),
);
```

### Concatenation

The `ConcatenationAdapter` allows querying results from multiple adapters. It keeps the order of the given adapters and the order of their results.

```php
<?php

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\ConcatenationAdapter;

$adapter = new ConcatenationAdapter(
    [
        new ArrayAdapter([]),
        new ArrayAdapter([]),
    ]
);
```

### Empty

<div class="docs-note docs-note--new-feature">The empty adapter was introduced in Pagerfanta 4.3.</div>

The `EmptyAdapter` provides an always empty result set, optimal for scenarios such as conditional returns to skip database queries where the application knows the parameters cannot produce a result set.

```php
<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\EmptyAdapter;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;

/*
 * Complex logic to set up parameters for a query, $shouldQuery is a pseudo-result of this step
 */

if (!$shouldQuery) {
    $adapter = new EmptyAdapter();
} else {
    $connection = DriverManager::getConnection([
        'driver' => 'pdo_sqlite',
        'memory' => true,
    ]);

    $query = $connection->createQueryBuilder()
        ->select('p.*')
        ->from('posts', 'p');

    $countQueryBuilderModifier = static function (QueryBuilder $queryBuilder): void {
        $queryBuilder->select('COUNT(DISTINCT p.id) AS total_results')
            ->setMaxResults(1);
    };

    $adapter = new QueryAdapter($query, $countQueryBuilderModifier);
}
```

#### Cursor Pagination

<div class="docs-note docs-note--new-feature">The <code>EmptyCursorAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `EmptyCursorAdapter` is the cursor pagination equivalent of the `EmptyAdapter`.

```php
<?php

use Pagerfanta\Adapter\EmptyCursorAdapter;

$adapter = new EmptyCursorAdapter();
```

### Fixed Size

The `FixedAdapter` takes a fixed data set and returns it no matter the request.

It is best used when you need to do a custom paging solution and don't want to implement a full adapter for a one-off use case.

```php
<?php

use Pagerfanta\Adapter\FixedAdapter;

$adapter = new FixedAdapter(5, ['boo', 'doo', 'foo', 'goo', 'moo']);
```

### Null Values

The `NullAdapter` generates a list of null values for the number of items specified, useful in a testing environment where you don't want to set up a database.

```php
<?php

use Pagerfanta\Adapter\NullAdapter;

$adapter = new NullAdapter(5);
```

### Transforming

The `TransformingAdapter` is an adapter decorator which can be used to standardize the data from the wrapped adapter.

The transformer is a callable which accepts the item to be transformed and the key from the iterable, it should have a signature of `function (mixed $item, int|string $key): mixed {}`

```php
<?php

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\TransformingAdapter;

$formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);

$adapter = new TransformingAdapter(
    new ArrayAdapter(range(1, 100)),
    static fn (int $item, int $key): string => $formatter->format($item)
);
```

#### Cursor Pagination

<div class="docs-note docs-note--new-feature">The <code>TransformingCursorAdapter</code> was introduced in Pagerfanta 4.10.</div>

The `TransformingCursorAdapter` is the cursor pagination equivalent of the `TransformingAdapter`. The cursors are created by the decorated adapter from the untransformed items, so the transformation does not affect navigation.

```php
<?php

use Pagerfanta\Adapter\ArrayCursorAdapter;
use Pagerfanta\Adapter\TransformingCursorAdapter;

$adapter = new TransformingCursorAdapter(
    new ArrayCursorAdapter($posts, static fn (array $post): array => ['id' => $post['id']]),
    static fn (array $post, int $key): string => $post['title']
);
```
