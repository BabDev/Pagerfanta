# Changelog

## 2.2.0 (2020-06-06)

- Added runtime deprecations for views when receiving a deprecated `Pagerfanta\PagerfantaInterface` implementation without being a `Pagerfanta\Pagerfanta` subclass
- Added a new `Pagerfanta\View\View` class which views can extend to re-use the pagination calculation logic
- Added a new `Pagerfanta\View\TemplateView` class which views that render `Pagerfanta\View\Template\TemplateInterface` instances can extend to re-use template related logic
- A `Pagerfanta\Exception\InvalidArgumentException` is now raised when the `$routeGenerator` is not a callable, as of 3.0 all methods will typehint the requirement
- Deprecated `Pagerfanta\Adapter\MandangoAdapter`, the dependent package is abandoned
- Deprecated `Pagerfanta\Adapter\PropelAdapter` and `Pagerfanta\Adapter\Propel2Adapter`
- Deprecated `Pagerfanta\Adapter\MongoAdapter` as it relies on the older `ext/mongo`
