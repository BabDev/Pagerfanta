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

The class constructor requires a `Doctrine\DBAL\Query\QueryBuilder` and a callable which can be used to modify a clone of the `QueryBuilder` for a COUNT query. The callable should have a signature of `function (QueryBuilder $queryBuilder): void {}`.

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

$countQueryBuilderModifier = static function (QueryBuilder $queryBuilder): void {
    $queryBuilder->select('COUNT(DISTINCT p.id) AS total_results')
        ->setMaxResults(1);
};

$adapter = new QueryAdapter($query, $countQueryBuilderModifier);
```

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

### Solarium

The Solarium adapter is available with the `pagerfanta/solarium-adapter` package for use with [Solarium](https://github.com/solariumphp/solarium).

```php
<?php

use Pagerfanta\Solarium\SolariumAdapter;

$query = $solarium->createSelect();
$query->setQuery('search term');

$adapter = new SolariumAdapter($solarium, $query);
```

## First Party

There are also several "first party" adapters which are not dependent upon an external storage solution. All first party adapters are available with the `pagerfanta/core` package.

### Array

The `ArrayAdapter` is used to paginate an array of items.

```php
<?php

use Pagerfanta\Adapter\ArrayAdapter;

$adapter = new ArrayAdapter([]);
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
