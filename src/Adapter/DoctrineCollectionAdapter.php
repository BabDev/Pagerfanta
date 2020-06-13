<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Doctrine\Collections\CollectionAdapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.4', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class from the "pagerfanta/doctrine-collections-adapter" package instead.', DoctrineCollectionAdapter::class, CollectionAdapter::class);

/**
 * Adapter which calculates pagination from a Doctrine Collection.
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\Doctrine\Collections\DoctrineCollectionsAdapter` from the `pagerfanta/doctrine-collections-adapter` package instead
 */
class DoctrineCollectionAdapter extends CollectionAdapter
{
}
