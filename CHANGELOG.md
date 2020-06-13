# Changelog

## 2.4.0 (2020-??-??)

- Add `Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface` representing a PHP class which builds a route generator at runtime
- Add `Pagerfanta\RouteGenerator\RouteGeneratorInterface` representing a PHP class fulfilling the route generator requirements

## 2.3.0 (2020-06-09)

- Change Composer package back to `pagerfanta/pagerfanta`
- Mark exceptions that are removed in 3.0 as deprecated

## 2.2.1 (2020-06-06)

- Remove types from `Pagerfanta\View\View` signatures

## 2.2.0 (2020-06-06)

- Added runtime deprecations for views when receiving a deprecated `Pagerfanta\PagerfantaInterface` implementation without being a `Pagerfanta\Pagerfanta` subclass
- Added a new `Pagerfanta\View\View` class which views can extend to re-use the pagination calculation logic
- Added a new `Pagerfanta\View\TemplateView` class which views that render `Pagerfanta\View\Template\TemplateInterface` instances can extend to re-use template related logic
- A `Pagerfanta\Exception\InvalidArgumentException` is now raised when the `$routeGenerator` is not a callable, as of 3.0 all methods will typehint the requirement
- Deprecated `Pagerfanta\Adapter\MandangoAdapter`, the dependent package is abandoned
- Deprecated `Pagerfanta\Adapter\PropelAdapter` and `Pagerfanta\Adapter\Propel2Adapter`
- Deprecated `Pagerfanta\Adapter\MongoAdapter` as it relies on the older `ext/mongo`
