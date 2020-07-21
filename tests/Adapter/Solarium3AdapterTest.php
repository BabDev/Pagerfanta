<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\SolariumAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use Solarium\Client;
use Solarium\Core\Client\Endpoint;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

class Solarium3AdapterTest extends SolariumAdapterTestCase
{
    protected function getSolariumName(): string
    {
        return 'Solarium 3';
    }

    /**
     * @return class-string
     */
    protected function getClientClass(): string
    {
        return Client::class;
    }

    /**
     * @return class-string
     */
    protected function getQueryClass(): string
    {
        return Query::class;
    }

    /**
     * @return class-string
     */
    protected function getResultClass(): string
    {
        return Result::class;
    }

    public function testGetResultSet(): void
    {
        $this->doTestGetResultSet($this->createQueryMock(), null);
    }

    public function testGetResultSetCanUseAnEndPoint(): void
    {
        $this->doTestGetResultSet($this->createQueryMock(), 'ups');
    }

    /**
     * @param MockObject|Query     $query
     * @param Endpoint|string|null $endpoint
     */
    private function doTestGetResultSet($query, $endpoint): void
    {
        $client = $this->createClientMock();
        $client->expects($this->atLeastOnce())
            ->method('select')
            ->with($query, $endpoint)
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        if (null !== $endpoint) {
            $adapter->setEndpoint($endpoint);
        }

        $this->assertInstanceOf($this->getResultClass(), $adapter->getResultSet());
    }
}
