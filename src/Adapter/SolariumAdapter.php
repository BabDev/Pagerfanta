<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Solarium\SolariumAdapter as PackageSolariumAdapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.4', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class from the "pagerfanta/solarium-adapter" package instead.', SolariumAdapter::class, PackageSolariumAdapter::class);

/**
 * Adapter which calculates pagination from a Solarium Query.
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\Solarium\SolariumAdapter` from the `pagerfanta/solarium-adapter` package instead
 */
class SolariumAdapter extends PackageSolariumAdapter
{
}
