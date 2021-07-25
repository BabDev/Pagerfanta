<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\SolariumAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Core\Client\Client;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

abstract class SolariumAdapterTestCase extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists($this->getClientClass())) {
            $this->markTestSkipped($this->getSolariumName().' is not available.');
        }
    }

    abstract protected function getSolariumName(): string;

    /**
     * @return class-string
     */
    abstract protected function getClientClass(): string;

    /**
     * @return class-string
     */
    abstract protected function getQueryClass(): string;

    /**
     * @return class-string
     */
    abstract protected function getResultClass(): string;

    /**
     * @return (MockObject&Client)|(MockObject&\Solarium_Client)
     */
    protected function createClientMock()
    {
        return $this->createMock($this->getClientClass());
    }

    /**
     * @return (MockObject&Query)|(MockObject&\Solarium_Query_Select)
     */
    protected function createQueryMock()
    {
        return $this->createMock($this->getQueryClass());
    }

    /**
     * @return (MockObject&Query)|(MockObject&\Solarium_Query_Select)
     */
    protected function createQueryStub()
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

    /**
     * @return (MockObject&Result)|(MockObject&\Solarium_Result_Select)
     */
    protected function createResultMock()
    {
        return $this->createMock($this->getResultClass());
    }

    public function testConstructorShouldThrowAnInvalidArgumentExceptionWhenInvalidClient(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SolariumAdapter(new \stdClass(), $this->createQueryMock());
    }

    public function testConstructorShouldThrowAnInvalidArgumentExceptionWhenInvalidQuery(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SolariumAdapter($this->createClientMock(), new \stdClass());
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
}
