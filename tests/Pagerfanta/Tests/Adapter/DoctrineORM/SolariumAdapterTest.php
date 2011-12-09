<?php

namespace Pagerfanta\Tests\Adapter;

class SolariumAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('Solarium_Client')) {
            $this->markTestSkipped('Solarium is not available.');
        }

        // ...
    }

    private function createSolariumClient($paths = array())
    {
        return new \Solarium_Client();
    }
}
