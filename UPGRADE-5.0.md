# Upgrade from 4.x to 5.0

The below guide will assist in upgrading from the 4.x versions to 5.0.

## Package Requirements

- PHP 8.2 or later

## General Changes

- Dropped support for versions of Doctrine DBAL before 3.9
- Dropped support for versions of Doctrine MongoDB ODM before 2.10
- Dropped support for versions of Doctrine ORM before 2.20 and 3.0 through 3.2
- Dropped support for versions of Doctrine PHPCR ODM before 2.0
- Dropped support for versions of Twig before 3.12
- The Doctrine DBAL query adapter requires the COUNT query builder modifier now to return a `Doctrine\DBAL\Query\QueryBuilder`

## Removed Features
