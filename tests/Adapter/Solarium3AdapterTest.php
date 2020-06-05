<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

class Solarium3AdapterTest extends SolariumAdapterTest
{
    protected function getSolariumName(): string
    {
        return 'Solarium 3';
    }

    protected function getClientClass(): string
    {
        return Client::class;
    }

    protected function getQueryClass(): string
    {
        return Query::class;
    }

    protected function getResultClass(): string
    {
        return Result::class;
    }
}
