<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Doctrine\PHPCRODM\QueryAdapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.4', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class from the "pagerfanta/doctrine-phpcr-odm-adapter" package instead.', DoctrineODMPhpcrAdapter::class, QueryAdapter::class);

/**
 * Adapter which calculates pagination from a Doctrine PHPCR ODM QueryBuilder.
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\Doctrine\PHPCRODM\QueryAdapter` from the `pagerfanta/doctrine-phpcr-odm-adapter` package instead
 */
class DoctrineODMPhpcrAdapter extends QueryAdapter
{
}
