<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\SolariumAdapter;

class Solarium2AdapterTest extends SolariumAdapterTestCase
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

    public function testGetResultSet(): void
    {
        $query = $this->createQueryMock();

        $client = $this->createClientMock();
        $client->expects($this->atLeastOnce())
            ->method('select')
            ->with($query)
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        $this->assertInstanceOf($this->getResultClass(), $adapter->getResultSet());
    }
}
