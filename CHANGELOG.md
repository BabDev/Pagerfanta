# Changelog

## 3.8.0 (????-??-??)

- Drop support for Twig versions prior to 2.13

## 3.7.0 (2022-12-02)

- Undeprecate `PagerfantaInterface::getAdapter()`
- Add support for `doctrine/collections` 2.x
- Drop support for `doctrine/dbal` 2.x

## 3.6.2 (2022-07-21)

- [#42](https://github.com/BabDev/Pagerfanta/pull/42) Add full signature for the `CallbackAdapter` callback
- [#44](https://github.com/BabDev/Pagerfanta/pull/44) Mark generic interfaces as being covariant

## 3.6.1 (2022-03-16)

- Remove `positive-int` typehint from setters where runtime checks are used

## 3.6.0 (2022-03-08)

- [#39](https://github.com/BabDev/Pagerfanta/pull/39) Add `TransformingAdapter`

## 3.5.2 (2022-01-24)

- Add generics annotations to `Pagerfanta\Adapter\AdapterInterface` and its implementations

## 3.5.1 (2021-12-08)

- Fix deprecations from Symfony's debug loader

## 3.5.0 (2021-11-30)

- Bump `doctrine/dbal` dependencies to `^2.13.1 || ^3.1`
- Allow v3 of `symfony/deprecation-contracts`

## 3.4.0 (2021-11-10)

- Added the static `Pagerfanta::createForCurrentPageWithMaxPerPage()` constructor to simplify `Pagerfanta` instance configuration

## 3.3.1 (2021-10-16)

- Calling `Pagerfanta::setMaxPerPage()` should not reset the number of results
- Avoid using deprecated `Doctrine\DBAL\Query\QueryBuilder::execute()` when able
- [#33](https://github.com/BabDev/Pagerfanta/pull/33) Check the current page after changing the max per page

## 3.3.0 (2021-08-08)

- [#31](https://github.com/BabDev/Pagerfanta/pull/31) Add Doctrine MongoDB ODM Aggregation adapter

## 3.2.1 (2021-08-01)

- Add generics annotations

## 3.2.0 (2021-06-30)

- Deprecate accessors for injected dependencies in `PagerfantaInterface` and `AdapterInterface` implementations

## 3.1.0 (2021-05-11)

- [#27](https://github.com/BabDev/Pagerfanta/pull/27) Add Foundation 6 templates

## 3.0.0 (2021-03-07)

- Consult the UPGRADE guide for changes between 2.x and 3.0

## 3.0 Beta 1 to Beta 2 (2021-02-02)

- B/C Break - Added `setMaxNbPages()` and `resetMaxNbPages()` methods to `PagerfantaInterface`
