<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Elastica\ElasticaAdapter as PackageElasticaAdapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.4', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class from the "pagerfanta/elastica-adapter" package instead.', ElasticaAdapter::class, PackageElasticaAdapter::class);

/**
 * Adapter which calculates pagination from a Elastica Query.
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\Elastica\ElasticaAdapter` from the `pagerfanta/elastica-adapter` package instead
 */
class ElasticaAdapter extends PackageElasticaAdapter
{
}
