<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\DoctrineORM\QueryAdapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.4', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class from the "pagerfanta/doctrine-orm-adapter" package instead.', DoctrineORMAdapter::class, QueryAdapter::class);

/**
 * Adapter which calculates pagination from a Doctrine ORM Query or QueryBuilder.
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\DoctrineORM\QueryAdapter` from the `pagerfanta/doctrine-orm-adapter` package instead
 */
class DoctrineORMAdapter extends QueryAdapter
{
}
