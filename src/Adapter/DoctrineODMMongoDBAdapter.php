<?php

namespace Pagerfanta\Adapter;

use Pagerfanta\Doctrine\MongoDBODM\QueryAdapter;

trigger_deprecation('pagerfanta/pagerfanta', '2.4', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class from the "pagerfanta/doctrine-mongodb-odm-adapter" package instead.', DoctrineODMMongoDBAdapter::class, QueryAdapter::class);

/**
 * Adapter which calculates pagination from a Doctrine MongoDB ODM QueryBuilder.
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\Doctrine\MongoDBODM\QueryAdapter` from the `pagerfanta/doctrine-mongodb-odm-adapter` package instead
 */
class DoctrineODMMongoDBAdapter extends QueryAdapter
{
}
