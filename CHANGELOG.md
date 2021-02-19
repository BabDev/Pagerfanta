# Changelog

## 2.7.0 (2021-02-18)

- Add Bootstrap 5 PHP and Twig templates 

## 2.6.0 (2021-02-02)

- Add support for setting a maximum number of pages

## 2.5.1 (2020-11-15)

- Add `doctrine/dbal` 3.0 support

## 2.5.0 (2020-11-10)

- Deprecated `Pagerfanta\View\Template\Template::$defaultOptions`, use the `Pagerfanta\View\Template\Template::getDefaultOptions()` method instead
- Allow install on PHP 8

## 2.4.1 (2020-08-03)

- Add `solarium/solarium` 6.0 support

## 2.4.0 (2020-07-25)

- Add `Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface` representing a PHP class which builds a route generator at runtime
- Add `Pagerfanta\RouteGenerator\RouteGeneratorInterface` representing a PHP class fulfilling the route generator requirements
- Added Twig integration
- Restructured package to support subtree splits, individual adapters and the core API may now be installed separately
- Undeprecated `Pagerfanta\PagerfantaInterface`

## 2.3.0 (2020-06-09)

- Change Composer package back to `pagerfanta/pagerfanta`
- Mark exceptions that are removed in 3.0 as deprecated

## 2.2.1 (2020-06-06)

- Remove types from `Pagerfanta\View\View` signatures

## 2.2.0 (2020-06-06)

- **B/C Break** Return typehints added to `Pagerfanta\View\Template\DefaultTemplate`, subclasses will need to be updated to account for this change
- Added runtime deprecations for views when receiving a deprecated `Pagerfanta\PagerfantaInterface` implementation without being a `Pagerfanta\Pagerfanta` subclass
- Added a new `Pagerfanta\View\View` class which views can extend to re-use the pagination calculation logic
- Added a new `Pagerfanta\View\TemplateView` class which views that render `Pagerfanta\View\Template\TemplateInterface` instances can extend to re-use template related logic
- A `Pagerfanta\Exception\InvalidArgumentException` is now raised when the `$routeGenerator` is not a callable, as of 3.0 all methods will typehint the requirement
- Deprecated `Pagerfanta\Adapter\MandangoAdapter`, the dependent package is abandoned
- Deprecated `Pagerfanta\Adapter\PropelAdapter` and `Pagerfanta\Adapter\Propel2Adapter`
- Deprecated `Pagerfanta\Adapter\MongoAdapter` as it relies on the older `ext/mongo`
