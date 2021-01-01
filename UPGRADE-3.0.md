# Upgrade from 2.x to 3.0

The below guide will assist in upgrading from the 2.x versions to 3.0.

## Package Requirements

- PHP 7.4 or later

## General Changes

- Dropped support for versions of Doctrine Collections before 1.6
- Dropped support for versions of Doctrine DBAL before 2.12
- Dropped support for versions of Doctrine ORM before 2.8
- Dropped support for versions of Doctrine PHPCR ODM before 1.5
- Dropped support for versions of Elastica before 6.0
- Dropped support for versions of Solarium before 5.0
- The `Pagerfanta\View\ViewFactory` class is now final
- `Pagerfanta\View\ViewFactoryInterface::remove()` is no longer required to throw an exception if the view to remove is not set
- Added `@method` annotated methods to their interfaces
- `Pagerfanta\PagerfantaInterface` now extends `Countable` and `IteratorAggregate`
- Renamed the `dots_text` option for the default template to `dots_message` for consistency with other templates and consistently use the HTML entity for an ellipsis instead of separated characters
- Renamed the `css_current_class` option for the default template to `css_active_class` for consistency with other templates
- The default template now supports the `css_container_class` option used by other view templates, if customizing the `container_template` option then a placeholder for a CSS class will need to be added
- The default template now supports the `css_prev_class` and `css_next_class` options used by other view templates, if customizing the `page_template` option then a placeholder for a CSS class will need to be added
- The class structure for the default template has changed and now uses a BEM based pattern
- The Bootstrap 3 view template will only set a default `active_suffix` option if one isn't provided
- Removed `Pagerfanta\View\Template\Template::$defaultOptions`, use the `getDefaultOptions()` method instead
- Removed arrows from default option values in templates

## Removed Features

- Removed the Mandango, Mongo, and Propel adapters
- Removed the deprecated adapters from the `Pagerfanta\Adapter` namespace, use the separate adapter packages instead
