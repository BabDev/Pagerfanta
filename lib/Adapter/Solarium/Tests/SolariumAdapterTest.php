<?php declare(strict_types=1);

namespace Pagerfanta\Solarium\Tests;

use Pagerfanta\Solarium\SolariumAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Core\Client\ClientInterface;
use Solarium\Core\Client\Endpoint;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

final class SolariumAdapterTest extends TestCase
{
    /**
     * @return MockObject&ClientInterface
     */
    protected function createClientMock()
    {
        return $this->createMock(ClientInterface::class);
    }

    /**
     * @return MockObject&Query
     */
    protected function createQueryMock()
    {
        return $this->createMock(Query::class);
    }

    /**
     * @return MockObject&Query
     */
    protected function createQueryStub()
    {
        $query = $this->createQueryMock();

        $query->method('setStart')
            ->willReturnSelf();

        $query->method('setRows')
            ->willReturnSelf();

        return $query;
    }

    /**
     * @return MockObject&Result
     */
    protected function createResultMock()
    {
        return $this->createMock(Result::class);
    }

    public function testGetNbResults(): void
    {
        $query = $this->createQueryMock();

        $result = $this->createResultMock();
        $result->expects(self::once())
            ->method('getNumFound')
            ->willReturn(100);

        $client = $this->createClientMock();
        $client->expects(self::once())
            ->method('select')
            ->with($query)
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        self::assertSame(100, $adapter->getNbResults());
    }

    public function testGetNbResultsCanUseACachedTheResultSet(): void
    {
        $query = $this->createQueryStub();

        $result = $this->createResultMock();
        $result->expects(self::atLeastOnce())
            ->method('getNumFound')
            ->willReturn(200);

        $client = $this->createClientMock();
        $client->expects(self::once())
            ->method('select')
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 1);
        $adapter->getNbResults();
    }

    public function testGetSlice(): void
    {
        $query = $this->createQueryMock();
        $query->method('setStart')
            ->with(1)
            ->willReturnSelf();

        $query->method('setRows')
            ->with(200)
            ->willReturnSelf();

        $result = $this->createResultMock();

        $client = $this->createClientMock();
        $client->expects(self::once())
            ->method('select')
            ->with($query)
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        self::assertSame($result, $adapter->getSlice(1, 200));
    }

    public function testGetSliceCannotUseACachedResultSet(): void
    {
        $query = $this->createQueryStub();

        $result = $this->createResultMock();
        $result->expects(self::atLeastOnce())
            ->method('getNumFound')
            ->willReturn(200);

        $client = $this->createClientMock();
        $client->expects(self::exactly(2))
            ->method('select')
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getNbResults();
        $adapter->getSlice(1, 200);
    }

    public function testGetNbResultCanUseAGetSliceCachedResultSet(): void
    {
        $query = $this->createQueryStub();

        $result = $this->createResultMock();
        $result->expects(self::atLeastOnce())
            ->method('getNumFound')
            ->willReturn(200);

        $client = $this->createClientMock();
        $client->expects(self::exactly(1))
            ->method('select')
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 200);
        $adapter->getNbResults();
    }

    public function testSameGetSliceUseACachedResultSet(): void
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client->expects(self::exactly(1))
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
        $client->expects(self::exactly(2))
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

    /**
     * @param MockObject&Query     $query
     * @param Endpoint|string|null $endpoint
     */
    private function doTestGetResultSet($query, $endpoint): void
    {
        $client = $this->createClientMock();
        $client->expects(self::atLeastOnce())
            ->method('select')
            ->with($query, $endpoint)
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        if (null !== $endpoint) {
            $adapter->setEndpoint($endpoint);
        }

        self::assertInstanceOf(Result::class, $adapter->getResultSet());
    }
}
