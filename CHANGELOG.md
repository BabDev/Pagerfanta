# Changelog

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
