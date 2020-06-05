## Unreleased

- Added runtime deprecations for views when receiving a deprecated `Pagerfanta\PagerfantaInterface` implementation without being a `Pagerfanta\Pagerfanta` subclass
- Added a new `Pagerfanta\View\View` class which views can extend to re-use the pagination calculation logic
- A `Pagerfanta\Exception\InvalidArgumentException` is now raised when the `$routeGenerator` is not a callable, as of 3.0 all methods will typehint the requirement
- Deprecated `Pagerfanta\Adapter\MandangoAdapter`, the dependent package is abandoned
- Deprecated `Pagerfanta\Adapter\PropelAdapter` and `Pagerfanta\Adapter\Propel2Adapter`
