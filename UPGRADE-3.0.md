# Upgrade from 2.x to 3.0

The below guide will assist in upgrading from the 2.x versions to 3.0.

## Package Requirements

- PHP 7.4 or later

## General Changes

- Dropped support for versions of Doctrine DBAL before 2.8
- Dropped support for versions of Doctrine ORM before 2.7
- Dropped support for versions of Elastica before 5.0
- Dropped support for versions of Solarium before 4.0
- The `Pagerfanta\View\ViewFactory` class is now final
- Added `@method` annotated methods to their interfaces
- `Pagerfanta\PagerfantaInterface` now extends `Countable` and `IteratorAggregate`
- Renamed the `dots_text` option for the default template to `dots_message` for consistency with other templates and consistently use the HTML entity for an ellipsis instead of separated characters
- The Bootstrap 3 view template will only set a default `active_suffix` option if one isn't provided

## Removed Features

- Removed the Mandango, Mongo, and Propel adapters
- Removed the deprecated adapters from the `Pagerfanta\Adapter` namespace, use the separate adapter packages instead
