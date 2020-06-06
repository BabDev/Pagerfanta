<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\SolariumAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Core\Client\ClientInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

class SolariumAdapterTest extends TestCase
{
    protected function createClientMock(): MockObject
    {
        return $this->createMock(ClientInterface::class);
    }

    protected function createQueryMock(): MockObject
    {
        return $this->createMock(Query::class);
    }

    protected function createQueryStub(): MockObject
    {
        $query = $this->createQueryMock();

        $query->expects($this->any())
            ->method('setStart')
            ->willReturnSelf();

        $query->expects($this->any())
            ->method('setRows')
            ->willReturnSelf();

        return $query;
    }

    protected function createResultMock(): MockObject
    {
        return $this->createMock(Result::class);
    }

    public function testGetNbResults(): void
    {
        $query = $this->createQueryMock();

        $result = $this->createResultMock();
        $result->expects($this->once())
            ->method('getNumFound')
            ->willReturn(100);

        $client = $this->createClientMock();
        $client->expects($this->once())
            ->method('select')
            ->with($query)
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        $this->assertSame(100, $adapter->getNbResults());
    }

    public function testGetNbResultsCanUseACachedTheResultSet(): void
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client->expects($this->once())
            ->method('select')
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 1);
        $adapter->getNbResults();
    }

    public function testGetSlice(): void
    {
        $query = $this->createQueryMock();
        $query->expects($this->any())
            ->method('setStart')
            ->with(1)
            ->willReturnSelf();

        $query->expects($this->any())
            ->method('setRows')
            ->with(200)
            ->willReturnSelf();

        $result = $this->createResultMock();

        $client = $this->createClientMock();
        $client->expects($this->once())
            ->method('select')
            ->with($query)
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        $this->assertSame($result, $adapter->getSlice(1, 200));
    }

    public function testGetSliceCannotUseACachedResultSet(): void
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client->expects($this->exactly(2))
            ->method('select')
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getNbResults();
        $adapter->getSlice(1, 200);
    }

    public function testGetNbResultCanUseAGetSliceCachedResultSet(): void
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client->expects($this->exactly(1))
            ->method('select')
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 200);
        $adapter->getNbResults();
    }

    public function testSameGetSliceUseACachedResultSet(): void
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client->expects($this->exactly(1))
            ->method('select')
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 200);
        $adapter->getSlice(1, 200);
    }

    public function testDifferentGetSliceCannotUseACachedResultSet(): void
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client->expects($this->exactly(2))
            ->method('select')
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 200);
        $adapter->getSlice(2, 200);
    }

    public function testGetResultSet(): void
    {
        $this->doTestGetResultSet($this->createQueryMock(), null);
    }

    public function testGetResultSetCanUseAnEndPoint(): void
    {
        $this->doTestGetResultSet($this->createQueryMock(), 'ups');
    }

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

        $this->assertInstanceOf(Result::class, $adapter->getResultSet());
    }
}
