<?php

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\SolariumAdapter;
use PHPUnit\Framework\TestCase;

abstract class SolariumAdapterTest extends TestCase
{
    abstract protected function getSolariumName();

    abstract protected function getClientClass();
    abstract protected function getQueryClass();
    abstract protected function getResultClass();

    public function setUp()
    {
        if ($this->isSolariumNotAvailable()) {
            $this->markTestSkipped($this->getSolariumName().' is not available.');
        }
    }

    private function isSolariumNotAvailable()
    {
        return !class_exists($this->getClientClass());
    }

    /**
     * @expectedException \Pagerfanta\Exception\InvalidArgumentException
     */
    public function testConstructorShouldThrowAnInvalidArgumentExceptionWhenInvalidClient()
    {
        new SolariumAdapter(new \ArrayObject(), $this->createQueryMock());
    }

    /**
     * @expectedException \Pagerfanta\Exception\InvalidArgumentException
     */
    public function testConstructorShouldThrowAnInvalidArgumentExceptionWhenInvalidQuery()
    {
        new SolariumAdapter($this->createClientMock(), new \ArrayObject());
    }

    public function testGetNbResults()
    {
        $query = $this->createQueryMock();
        $endPoint = null;

        $result = $this->createResultMock();
        $result
            ->expects($this->once())
            ->method('getNumFound')
            ->will($this->returnValue(100));

        $client = $this->createClientMock();
        $client
            ->expects($this->once())
            ->method('select')
            ->with($query, $endPoint)
            ->will($this->returnValue($result));

        $adapter = new SolariumAdapter($client, $query);

        $this->assertSame(100, $adapter->getNbResults());
    }

    public function testGetNbResultsCanUseACachedTheResultSet()
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client
            ->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->createResultMock()));

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 1);
        $adapter->getNbResults();
    }

    public function testGetSlice()
    {
        $query = $this->createQueryMock();
        $query
            ->expects($this->any())
            ->method('setStart')
            ->with(1)
            ->will($this->returnValue($query));
        $query
            ->expects($this->any())
            ->method('setRows')
            ->with(200)
            ->will($this->returnValue($query));

        $endPoint = null;
        $result = $this->createResultMock();

        $client = $this->createClientMock();
        $client
            ->expects($this->once())
            ->method('select')
            ->with($query, $endPoint)
            ->will($this->returnValue($result));

        $adapter = new SolariumAdapter($client, $query);

        $this->assertSame($result, $adapter->getSlice(1, 200));
    }

    public function testGetSliceCannotUseACachedResultSet()
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client
            ->expects($this->exactly(2))
            ->method('select')
            ->will($this->returnValue($this->createResultMock()));

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getNbResults();
        $adapter->getSlice(1, 200);
    }

    public function testGetNbResultCanUseAGetSliceCachedResultSet()
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client
            ->expects($this->exactly(1))
            ->method('select')
            ->will($this->returnValue($this->createResultMock()));

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 200);
        $adapter->getNbResults();
    }

    public function testSameGetSliceUseACachedResultSet()
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client
            ->expects($this->exactly(1))
            ->method('select')
            ->will($this->returnValue($this->createResultMock()));

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 200);
        $adapter->getSlice(1, 200);
    }

    public function testDifferentGetSliceCannotUseACachedResultSet()
    {
        $query = $this->createQueryStub();

        $client = $this->createClientMock();
        $client
            ->expects($this->exactly(2))
            ->method('select')
            ->will($this->returnValue($this->createResultMock()));

        $adapter = new SolariumAdapter($client, $query);

        $adapter->getSlice(1, 200);
        $adapter->getSlice(2, 200);
    }

    public function testGetResultSet()
    {
        $query = $this->createQueryMock();
        $endPoint = null;

        $this->doTestGetResultSet($query, $endPoint);
    }

    public function testGetResultSetCanUseAnEndPoint()
    {
        $query = $this->createQueryMock();
        $endPoint = 'ups';

        $this->doTestGetResultSet($query, $endPoint);
    }

    private function doTestGetResultSet($query, $endPoint)
    {
        $client = $this->createClientMock();
        $client
            ->expects($this->atLeastOnce())
            ->method('select')
            ->with($query, $endPoint)
            ->will($this->returnValue($this->createResultMock()));

        $adapter = new SolariumAdapter($client, $query);
        if ($endPoint !== null) {
            $adapter->setEndPoint($endPoint);
        }

        $this->assertInstanceOf($this->getResultClass(), $adapter->getResultSet());
    }

    private function createClientMock()
    {
        return $this->getMockBuilder($this->getClientClass())
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createQueryMock()
    {
        return $this->getMockBuilder($this->getQueryClass())->getMock();
    }

    private function createQueryStub()
    {
        $query = $this->createQueryMock();
        $query
            ->expects($this->any())
            ->method('setStart')
            ->will($this->returnSelf());
        $query
            ->expects($this->any())
            ->method('setRows')
            ->will($this->returnSelf());

        return $query;
    }

    private function createResultMock()
    {
        return $this->getMockBuilder($this->getResultClass())
            ->disableOriginalConstructor()
            ->getMock();
    }
}
