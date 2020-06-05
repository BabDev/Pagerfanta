<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\DoctrineDbalSingleTableAdapter;
use Pagerfanta\Exception\InvalidArgumentException;

class DoctrineDbalSingleTableAdapterTest extends DoctrineDbalTestCase
{
    /**
     * @var DoctrineDbalSingleTableAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = new DoctrineDbalSingleTableAdapter($this->qb, 'p.id');
    }

    public function testGetNbResults(): void
    {
        $this->doTestGetNbResults();
    }

    public function testGetNbResultsShouldWorkAfterCallingGetSlice(): void
    {
        $this->adapter->getSlice(1, 10);

        $this->doTestGetNbResults();
    }

    private function doTestGetNbResults(): void
    {
        $this->assertSame(50, $this->adapter->getNbResults());
    }

    public function testGetNbResultWithNoData(): void
    {
        $q = clone $this->qb;
        $q->delete('posts')->execute();

        $this->assertSame(0, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $this->doTestGetSlice();
    }

    public function testGetSliceShouldWorkAfterCallingGetNbResults(): void
    {
        $this->adapter->getNbResults();

        $this->doTestGetSlice();
    }

    private function doTestGetSlice(): void
    {
        $offset = 30;
        $length = 10;

        $q = clone $this->qb;
        $q->setFirstResult($offset)->setMaxResults($length);
        $expectedResults = $q->execute()->fetchAll();

        $results = $this->adapter->getSlice($offset, $length);
        $this->assertSame($expectedResults, $results);
    }

    public function testItShouldThrowAnInvalidArgumentExceptionIfTheCountFieldDoesNotHaveAlias(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DoctrineDbalSingleTableAdapter($this->qb, 'id');
    }

    public function testItShouldThrowAnInvalidArgumentExceptionIfTheQueryHasJoins(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->qb->innerJoin('p', 'comments', 'c', 'c.post_id = p.id');

        new DoctrineDbalSingleTableAdapter($this->qb, 'p.id');
    }
}
