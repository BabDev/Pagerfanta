# Available Adapters

The Pagerfanta package provides out-of-the-box support for a number of storage backends.

## Third Party

Adapters are provided for a number of third party storage solutions, allowing this package to be used in a variety of environments.

### Doctrine

Adapters are available for a number of [Doctrine](https://www.doctrine-project.org/) packages.

#### Collections

The `DoctrineCollectionAdapter` and `DoctrineSelectableAdapter` are available when using the [Doctrine Collections](https://www.doctrine-project.org/projects/collections.html) package.

Below is an example of using the `DoctrineCollectionAdapter` on a collection from an entity.

```php
<?php

use App\Entity\User;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;

$config = new Configuration();

$connection = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];

$em = EntityManager::create($connection, $config);

$user = $em->find(User::class, 1);

$adapter = new DoctrineCollectionAdapter($user->getGroups());
```

Below is an example of using the `DoctrineSelectableAdapter` on a class which implements `Doctrine\Common\Collection\Selectable` (such as a `Doctrine\ORM\PersistentCollection`).

```php
<?php

use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;

$config = new Configuration();

$connection = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];

$em = EntityManager::create($connection, $config);

$user = $em->find(User::class, 1);

$criteria = Criteria::create()->andWhere(Criteria::expr()->in('id', [1, 2, 3]));

$adapter = new DoctrineSelectableAdapter($user->getGroups(), $criteria);
```

#### DBAL

The `DoctrineDbalAdapter` and `DoctrineDbalSingleTableAdapter` are available when using the [Doctrine DBAL](https://www.doctrine-project.org/projects/dbal.html) package.

The `DoctrineDbalSingleTableAdapter` is a helper class which is optimized for queries which do not have any join statements.

The class constructor requires a `Doctrine\DBAL\Query\QueryBuilder` and the field name that should be counted (typically this will be your primary key).

<div class="docs-note">Using this adapter requires that you have a table alias for your query.</div>

Below is an example of using the `DoctrineDbalSingleTableAdapter`.

```php
<?php

use Doctrine\DBAL\DriverManager;
use Pagerfanta\Adapter\DoctrineDbalSingleTableAdapter;

$params = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];

$connection = DriverManager::getConnection($params);

$query = $connection->createQueryBuilder()
    ->select('p.*')
    ->from('posts', 'p');

$adapter = new DoctrineDbalSingleTableAdapter($query, 'p.id');
```

The `DoctrineDbalAdapter` is the main adapter for use with the DBAL package, you should use this on queries that have join statements.

The class constructor requires a `Doctrine\DBAL\Query\QueryBuilder` and a callable which can be used to modify a clone of the `QueryBuilder` for a COUNT query. The callable should have a signature of `function (QueryBuilder $queryBuilder): void {}`.

Below is an example of using the `DoctrineDbalAdapter`.

```php
<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

$params = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];

$connection = DriverManager::getConnection($params);

$query = $connection->createQueryBuilder()
    ->select('p.*')
    ->from('posts', 'p');

$countQueryBuilderModifier = function (QueryBuilder $queryBuilder): void {
    $queryBuilder->select('COUNT(DISTINCT p.id) AS total_results')
        ->setMaxResults(1);
};

$adapter = new DoctrineDbalAdapter($query, $countQueryBuilderModifier);
```

#### MongoDB ODM

The `DoctrineODMMongoDBAdapter` is available when using the [Doctrine MongoDB ODM](https://www.doctrine-project.org/projects/mongodb-odm.html) package.

The class constructor requires a `Doctrine\ODM\MongoDB\Query\Builder`.

Below is an example of using the `DoctrineODMMongoDBAdapter`.

```php
<?php

use App\Document\Article;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;

$config = new Configuration();

$dm = DocumentManager::create(null, $config);

$query = $dm->createQueryBuilder(Article::class);

$adapter = new DoctrineODMMongoDBAdapter($query);
```

#### ORM

The `DoctrineORMAdapter` is available when using the [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html) package.

The class constructor requires either a `Doctrine\ORM\Query` or `Doctrine\ORM\QueryBuilder` instance. You can also specify whether to query join collections or use output walkers on the underlying [`Paginator`](https://www.doctrine-project.org/projects/doctrine-orm/en/current/tutorials/pagination.html#pagination).

Below is an example of using the `DoctrineORMAdapter`.

```php
<?php

use App\Entity\User;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;

$config = new Configuration();

$connection = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];

$em = EntityManager::create($connection, $config);

$repository = $em->getRepository(User::class);

$query = $repository->createQueryBuilder('u');

$adapter = new DoctrineORMAdapter($query);
```

#### PHPCR ODM

The `DoctrineODMPhpcrAdapter` is available when using the [Doctrine PHPCR ODM](https://www.doctrine-project.org/projects/phpcr-odm.html) package.

The class constructor requires a `Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder`.

Below is an example of using the `DoctrineODMPhpcrAdapter`.

```php
<?php

use App\Document\Article;
use Doctrine\ODM\PHPCR\Configuration;
use Doctrine\ODM\PHPCR\DocumentManager;
use Pagerfanta\Adapter\DoctrineODMPhpcrAdapter;

$config = new Configuration();

$dm = DocumentManager::create($session, $config);

$query = $dm->createQueryBuilder()
    ->from(Article::class);

$adapter = new DoctrineODMPhpcrAdapter($query);
```

### Elastica

An adapter is available for the [Elastica](https://elastica.io/) package.

```php
<?php

use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Term;
use Pagerfanta\Adapter\ElasticaAdapter;

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

<div class="docs-note">Be careful when paginating a huge set of documents. By default, offset + limit cannot exceed 10,000 items. You can mitigate this by setting the `$maxResults` parameter when constructing the `ElasticaAdapter`. For more information, see [https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892](https://github.com/whiteoctober/Pagerfanta/pull/213#issue-87631892).</div>

### Mandango

<div class="docs-note docs-note--deprecated-feature">This adapter is deprecated as of Pagerfanta 2.2 and will be removed in 3.0.</div>

An adapter is available for the [Mandango](https://github.com/mandango/mandango) package.

```php
<?php

use App\Document\Article;
use Pagerfanta\Adapter\MandangoAdapter;

$query = $mandango->getRepository(Article::class)->createQuery();
$adapter = new MandangoAdapter($query);
```

### Mongo

<div class="docs-note docs-note--deprecated-feature">This adapter is deprecated as of Pagerfanta 2.2 and will be removed in 3.0.</div>

An adapter is available for the [mongo](https://pecl.php.net/package/mongo) PHP extension.

```php
<?php

use Pagerfanta\Adapter\MongoAdapter;

$cursor = $collection->find();
$adapter = new MongoAdapter($cursor);
```

### Propel

<div class="docs-note docs-note--deprecated-feature">These adapters are deprecated as of Pagerfanta 2.2 and will be removed in 3.0.</div>

Adapters are available for versions 1 and 2 of the [Propel ORM](http://propelorm.org/).

#### Propel1

```php
<?php

use Pagerfanta\Adapter\PropelAdapter;

$adapter = new PropelAdapter($query);
```

#### Propel2

```php
<?php

use Pagerfanta\Adapter\Propel2Adapter;

$adapter = new Propel2Adapter($query);
```

### Solarium

An adapter is available for the [Solarium](https://github.com/solariumphp/solarium) package.

```php
<?php

use Pagerfanta\Adapter\SolariumAdapter;

$query = $solarium->createSelect();
$query->setQuery('search term');

$adapter = new SolariumAdapter($solarium, $query);
```

## First Party

There are also several "first party" adapters which are not dependent upon an external storage solution.

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
    function (): int { return 0; },
    function (int $offset, int $length): iterable { return []; }
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
