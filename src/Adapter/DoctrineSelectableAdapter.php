<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\DoctrineCollections\SelectableAdapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.4', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class from the "pagerfanta/doctrine-collections-adapter" package instead.', DoctrineSelectableAdapter::class, SelectableAdapter::class);

/**
 * Adapter which calculates pagination from a Selectable instance.
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\DoctrineCollections\SelectableAdapter` from the `pagerfanta/doctrine-collections-adapter` package instead
 */
class DoctrineSelectableAdapter extends SelectableAdapter
{
}
