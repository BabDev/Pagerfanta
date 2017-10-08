<?php

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\ConcatenationAdapter;

class ConcatenationAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testGetNbResults()
    {
        $adapter = new ConcatenationAdapter(array(
            new ArrayAdapter(array('foo', 'bar', 'baz'))
        ));
        $this->assertEquals(3, $adapter->getNbResults());

        $adapter = new ConcatenationAdapter(array(
            new ArrayAdapter(array_fill(0, 4, 'foo')),
            new ArrayAdapter(array_fill(0, 6, 'bar')),
            new ArrayAdapter(array('baq'))
        ));
        $this->assertEquals(11, $adapter->getNbResults());

        $adapter = new ConcatenationAdapter(array());
        $this->assertEquals(0, $adapter->getNbResults());
    }

    public function testGetResults()
    {
        $adapter = new ConcatenationAdapter(array(
            new ArrayAdapter(array(1, 2, 3, 4, 5, 6)),
            new ArrayAdapter(array(7, 8, 9, 10, 11, 12, 13, 14)),
            new ArrayAdapter(array(15, 16, 17))
        ));
        $this->assertEquals(array(8, 9, 10), $adapter->getSlice(7, 3));
        $this->assertEquals(array(5, 6, 7, 8), $adapter->getSlice(4, 4));
        $this->assertEquals(array(6, 7, 8, 9, 10, 11, 12, 13, 14, 15), $adapter->getSlice(5, 10));
        $this->assertEquals(array(16, 17), $adapter->getSlice(15, 5));
    }
}
