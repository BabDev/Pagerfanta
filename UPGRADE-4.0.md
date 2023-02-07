# Upgrade from 3.x to 4.0

The below guide will assist in upgrading from the 3.x versions to 4.0.

## Package Requirements

- PHP 8.1 or later

## General Changes

- Dropped support for versions of Doctrine DBAL before 3.5
- Dropped support for versions of Doctrine MongoDB ODM before 2.4
- Dropped support for versions of Doctrine ORM before 2.14
- Dropped support for versions of Doctrine PHPCR ODM before 1.7
- Dropped support for versions of Elastica before 7.3
- Dropped support for versions of Solarium before 6.2
- `Pagerfanta\Adapter\AdapterInterface::getNbResults()` can now throw a `Pagerfanta\Exception\NotValidResultCountException` if the result count is less than zero
- The `Pagerfanta\Adapter\FixedAdapter` constructor will now throw a `Pagerfanta\Exception\NotValidResultCountException` if the result count is less than zero
- The `Pagerfanta\Elastica\ElasticaAdapter` constructor will now throw a `Pagerfanta\Exception\NotValidResultCountException` if the max result count is less than zero

## Removed Features

- Removed `Pagerfanta\Adapter\ArrayAdapter::getArray()`
- Removed `Pagerfanta\Doctrine\Collections\CollectionAdapter::getCollection()`
- Removed `Pagerfanta\Doctrine\MongoDBODM\QueryAdapter::getQueryBuilder()`
- Removed `Pagerfanta\Doctrine\ORM\QueryAdapter::getFetchJoinCollection()`
- Removed `Pagerfanta\Doctrine\ORM\QueryAdapter::getQuery()`
- Removed `Pagerfanta\Doctrine\PHPCRODM\QueryAdapter::getQueryBuilder()`
