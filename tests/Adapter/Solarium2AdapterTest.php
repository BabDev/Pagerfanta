<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

class Solarium2AdapterTest extends SolariumAdapterTest
{
    protected function getSolariumName(): string
    {
        return 'Solarium 2';
    }

    protected function getClientClass(): string
    {
        return \Solarium_Client::class;
    }

    protected function getQueryClass(): string
    {
        return \Solarium_Query_Select::class;
    }

    protected function getResultClass(): string
    {
        return \Solarium_Result_Select::class;
    }
}
