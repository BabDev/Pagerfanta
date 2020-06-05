<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\SolariumAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

abstract class SolariumAdapterTest extends TestCase
{
    abstract protected function getSolariumName(): string;

    abstract protected function getClientClass(): string;

    abstract protected function getQueryClass(): string;

    abstract protected function getResultClass(): string;

    protected function setUp(): void
    {
        if ($this->isSolariumNotAvailable()) {
            $this->markTestSkipped($this->getSolariumName().' is not available.');
        }
    }

    private function isSolariumNotAvailable(): bool
    {
        return !class_exists($this->getClientClass());
    }

    public function testConstructorShouldThrowAnInvalidArgumentExceptionWhenInvalidClient(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SolariumAdapter(new \ArrayObject(), $this->createQueryMock());
    }

    public function testConstructorShouldThrowAnInvalidArgumentExceptionWhenInvalidQuery(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SolariumAdapter($this->createClientMock(), new \ArrayObject());
    }

    public function testGetNbResults(): void
    {
        $query = $this->createQueryMock();
        $endPoint = null;

        $result = $this->createResultMock();
        $result
            ->expects($this->once())
            ->method('getNumFound')
            ->willReturn(100);

        $client = $this->createClientMock();
        $client
            ->expects($this->once())
            ->method('select')
            ->with($query, $endPoint)
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        $this->assertSame(100, $adapter->getNbResults());
    }

    public function testGetNbResultsCanUseACachedTheResultSet(): void
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client
            ->expects($this->once())
            ->method('select')
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 1);
        $adapter->getNbResults();
    }

    public function testGetSlice(): void
    {
        $query = $this->createQueryMock();
        $query
            ->expects($this->any())
            ->method('setStart')
            ->with(1)
            ->willReturn($query);
        $query
            ->expects($this->any())
            ->method('setRows')
            ->with(200)
            ->willReturn($query);

        $endPoint = null;
        $result = $this->createResultMock();

        $client = $this->createClientMock();
        $client
            ->expects($this->once())
            ->method('select')
            ->with($query, $endPoint)
            ->willReturn($result);

        $adapter = new SolariumAdapter($client, $query);

        $this->assertSame($result, $adapter->getSlice(1, 200));
    }

    public function testGetSliceCannotUseACachedResultSet(): void
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client
            ->expects($this->exactly(2))
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
        $client
            ->expects($this->exactly(1))
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
        $client
            ->expects($this->exactly(1))
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
        $client
            ->expects($this->exactly(2))
            ->method('select')
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 200);
        $adapter->getSlice(2, 200);
    }

    public function testGetResultSet(): void
    {
        $query = $this->createQueryMock();
        $endPoint = null;

        $this->doTestGetResultSet($query, $endPoint);
    }

    public function testGetResultSetCanUseAnEndPoint(): void
    {
        $query = $this->createQueryMock();
        $endPoint = 'ups';

        $this->doTestGetResultSet($query, $endPoint);
    }

    private function doTestGetResultSet($query, $endPoint): void
    {
        $client = $this->createClientMock();
        $client
            ->expects($this->atLeastOnce())
            ->method('select')
            ->with($query, $endPoint)
            ->willReturn($this->createResultMock());

        $adapter = new SolariumAdapter($client, $query);
        if (null !== $endPoint) {
            $adapter->setEndPoint($endPoint);
        }

        $this->assertInstanceOf($this->getResultClass(), $adapter->getResultSet());
    }

    private function createClientMock()
    {
        return $this->createMock($this->getClientClass());
    }

    private function createQueryMock()
    {
        return $this->createMock($this->getQueryClass());
    }

    private function createQueryStub()
    {
        $query = $this->createQueryMock();
        $query
            ->expects($this->any())
            ->method('setStart')
            ->willReturnSelf();
        $query
            ->expects($this->any())
            ->method('setRows')
            ->willReturnSelf();

        return $query;
    }

    private function createResultMock()
    {
        return $this->createMock($this->getResultClass());
    }
}
