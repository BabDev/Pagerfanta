# 1. Positions are a marker interface

- Status: Accepted
- Date: 2026-09-25
- Tracking issue: [#30](https://github.com/BabDev/Pagerfanta/issues/30)

## Context

Pagerfanta is adding cursor pagination alongside the existing offset pagination. Route generators and views need to link to
the "previous" and "next" pages without knowing which strategy the pager uses. Offset pagers identify a page by its number,
while cursor pagers identify a page by a cursor holding sort key values and a direction.

The root pager interface therefore needs a strategy-independent way to describe "where" a link points. Two options were
considered:

1. **Marker interface**: an empty `Pagerfanta\Position\Position` interface, implemented by `PagePosition` (a page number)
   and `CursorPosition` (a cursor). Pagers return positions, and route generators accept a `Position`.
2. **Generics only**: no shared type at runtime. The root pager declares `@template TPosition` and the position methods return
   `mixed` (an `int` for offset pagers, a cursor for cursor pagers).

## Decision

Positions are a marker interface (option 1). The root `PagerInterface` also declares `@template TPosition of Position` so that
static analysis can narrow the position type for the offset (`PagePosition`) and cursor (`CursorPosition`) pagers.

## Consequences

- Route generators and views stay simply typed: `__invoke(Position $position): string` works for every strategy, and
  implementations dispatch with `instanceof`.
- Each generated link allocates a small value object. This is negligible compared to rendering the link.
- Offset pagers wrap page numbers in `PagePosition`, so existing `int` based APIs (`getPreviousPage()`, `getNextPage()`,
  `RouteGeneratorInterface`) remain in 4.x. `PageRouteGeneratorWrapper` adapts `int` based route generators to the position
  based API.
- A generator given a position it does not support (e.g. an `int` based generator given a `CursorPosition`) throws an
  `InvalidArgumentException` rather than silently producing a wrong URL.
- The generics-only approach would have pushed `mixed` into every generator and view signature, losing runtime type safety
  for code not analyzed with PHPStan or Psalm.
