# Changelog

## 4.8.0 (2026-01-22)

- Add support for `doctrine/collections` 3.x

## 4.7.2 (2025-10-12)

- [#62](https://github.com/BabDev/Pagerfanta/issues/62) Remove some PHPStan annotations from `Pagerfanta\PagerfantaInterface` and `Pagerfanta\Pagerfanta` to resolve downstream compatibility issues

## 4.7.1 (2024-12-13)

- Misc. type updates (internally, the library has also updated to PHPStan 2.0)

## 4.7.0 (2024-08-13)

- [#56](https://github.com/BabDev/Pagerfanta/issues/56) Add support for `Doctrine\Common\Collections\ReadableCollection` in `Pagerfanta\Doctrine\Collections\CollectionAdapter`

## 4.6.0 (2024-05-29)

- Add support for `ruflin/elastica` 8.x
- Deprecate not returning a query builder from the modifier callback in `Pagerfanta\Doctrine\DBAL\QueryAdapter`

## 4.5.0 (2024-04-10)

- Add support for `doctrine/phpcr-odm` 2.x

## 4.4.0 (2024-03-24)

- [#55](https://github.com/BabDev/Pagerfanta/pull/55) Add an EmptyAdapter

## 4.3.2 (2024-03-06)

- [#54](https://github.com/BabDev/Pagerfanta/pull/54) Add Psalm annotations to the `Pagerfanta::createForCurrentPageWithMaxPerPage()` static constructor

## 4.3.1 (2024-01-31)

- [#53](https://github.com/BabDev/Pagerfanta/pull/53) Fix Doctrine's `Criteria::setFirstResult()` method usage deprecation

## 4.3.0 (2024-01-27)

- Add support for `doctrine/dbal` 4.x
- Add support for `doctrine/orm` 3.x

## 4.2.0 (2023-07-10)

- [#50](https://github.com/BabDev/Pagerfanta/pull/50) Add `page` variable to Twig blocks where missing

## 4.1.0 (2023-04-15)

- [#48](https://github.com/BabDev/Pagerfanta/pull/48) Add support for memory efficient auto-pagination

## 4.0.0 (2023-03-15)

- Consult the UPGRADE guide for changes between 3.x and 4.0
