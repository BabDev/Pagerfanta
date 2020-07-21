<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Doctrine\DBAL\QueryAdapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.4', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class from the "pagerfanta/doctrine-dbal-adapter" package instead.', DoctrineDbalAdapter::class, QueryAdapter::class);

/**
 * Adapter which calculates pagination from a Doctrine DBAL QueryBuilder.
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\Doctrine\DBAL\QueryAdapter` from the `pagerfanta/doctrine-dbal-adapter` package instead
 */
class DoctrineDbalAdapter extends QueryAdapter
{
}
