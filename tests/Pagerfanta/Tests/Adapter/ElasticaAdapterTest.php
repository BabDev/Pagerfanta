<?php

namespace Pagerfanta\Tests\Adapter;

use Elastica\Response;
use Elastica\ResultSet;
use Pagerfanta\Adapter\ElasticaAdapter;

class ElasticaAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ElasticaAdapter
     */
    private $adapter;
    private $resultSet;
    private $searchable;
    private $query;

    protected function setUp()
    {
        $this->query = $this->getMockBuilder('Elastica\\Query')->disableOriginalConstructor()->getMock();
        $this->resultSet = $this->getMockBuilder('Elastica\\ResultSet')->disableOriginalConstructor()->getMock();
        $this->searchable = $this->getMockBuilder('Elastica\\SearchableInterface')->disableOriginalConstructor()->getMock();

        $this->adapter = new ElasticaAdapter($this->searchable, $this->query);

        $this->searchable->expects($this->any())
            ->method('search')
            ->with($this->query)
            ->will($this->returnValue($this->resultSet));
    }

    public function testGetResultSet()
    {
        $this->assertNull($this->adapter->getResultSet());

        $this->searchable->expects($this->any())
            ->method('search')
            ->with($this->query, array('from' => 0, 'size' => 1))
            ->will($this->returnValue($this->resultSet));

        $this->adapter->getSlice(0, 1);

        $this->assertSame($this->resultSet, $this->adapter->getResultSet());
    }

    public function testGetSlice()
    {
        $this->searchable->expects($this->any())
            ->method('search')
            ->with($this->query, array('from' => 10, 'size' => 30))
            ->will($this->returnValue($this->resultSet));

        $resultSet = $this->adapter->getSlice(10, 30);

        $this->assertSame($this->resultSet, $resultSet);
        $this->assertSame($this->resultSet, $this->adapter->getResultSet());
    }

    public function testGetNbResults()
    {
        $this->resultSet->expects($this->once())
            ->method('getTotalHits')
            ->will($this->returnValue(100));

        $this->assertSame(100, $this->adapter->getNbResults());
    }
}
