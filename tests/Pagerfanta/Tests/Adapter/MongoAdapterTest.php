<?php

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\MongoAdapter;

class MongoAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $cursor;
    protected $adapter;

    protected function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('Mongo extension is not loaded');
        }

        $this->cursor = $this
            ->getMockBuilder('\MongoCursor')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->adapter = new MongoAdapter($this->cursor);
    }

    public function testGetCursor()
    {
        $this->assertSame($this->cursor, $this->adapter->getCursor());
    }

    public function testGetNbResults()
    {
        $this->cursor
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(100))
        ;

        $this->assertSame(100, $this->adapter->getNbResults());
    }

    /**
     * @dataProvider getResultsProvider
     */
    public function testGetResults($offset, $length)
    {
        $this->cursor
            ->expects($this->once())
            ->method('limit')
            ->with($length)
            ->will($this->returnValue($this->cursor))
        ;
        $this->cursor
            ->expects($this->once())
            ->method('skip')
            ->with($offset)
            ->will($this->returnValue($this->cursor))
        ;
        $this->cursor
            ->expects($this->any())
            ->method('valid')
            ->will($this->onConsecutiveCalls(true, true, false))
        ;
        $this->cursor
            ->expects($this->any())
            ->method('current')
            ->will($this->returnValue($value = new \DateTime()))
        ;
        $this->cursor
            ->expects($this->any())
            ->method('key')
            ->will($this->onConsecutiveCalls(0, 1))
        ;

        $this->assertSame(array($value, $value), $this->adapter->getSlice($offset, $length));
    }

    public function getResultsProvider()
    {
        return array(
            array(2, 10),
            array(3, 2),
        );
    }
}
