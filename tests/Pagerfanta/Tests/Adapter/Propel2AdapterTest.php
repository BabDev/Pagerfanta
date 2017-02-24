<?php

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\Propel2Adapter;

/**
 * Propel2AdapterTest
 *
 * @author Claude Khedhiri <claude@khedhiri.com>
 */
class Propel2AdapterTest extends \PHPUnit_Framework_TestCase
{
    private $query;

    /**
     * @var Propel2Adapter
     */
    private $adapter;

    protected function setUp()
    {
        if ($this->isPropel2NotAvaiable()) {
            $this->markTestSkipped('Propel 2 is not available');
        }

        $this->query = $this->createQueryMock();
        $this->adapter = new Propel2Adapter($this->query);
    }

    private function isPropel2NotAvaiable()
    {
        return !class_exists('Propel\Runtime\ActiveQuery\ModelCriteria');
    }

    private function createQueryMock()
    {
        return $this
            ->getMockBuilder('Propel\Runtime\ActiveQuery\ModelCriteria')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetQuery()
    {
        $this->assertSame($this->query, $this->adapter->getQuery());
    }

    public function testGetNbResults()
    {
        $this->query
            ->expects($this->once())
            ->method('offset')
            ->with(0);
        $this->query
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(100));

        $this->assertSame(100, $this->adapter->getNbResults());
    }

    public function testGetSlice()
    {
        $offset = 14;
        $length = 20;
        $slice = new \ArrayObject();

        $this->query
            ->expects($this->once())
            ->method('limit')
            ->with($length);
        $this->query
            ->expects($this->once())
            ->method('offset')
            ->with($offset);
        $this->query
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($slice));

        $this->assertSame($slice, $this->adapter->getSlice($offset, $length));
    }
}
