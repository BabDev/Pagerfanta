<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

class DoctrineDbalAdapterTest extends DoctrineDbalTestCase
{
    public function testGetNbResults(): void
    {
        $adapter = $this->createAdapterToTestGetNbResults();

        $this->doTestGetNbResults($adapter);
    }

    public function testGetNbResultsShouldWorkAfterCallingGetSlice(): void
    {
        $adapter = $this->createAdapterToTestGetNbResults();

        $adapter->getSlice(1, 10);

        $this->doTestGetNbResults($adapter);
    }

    private function doTestGetNbResults(DoctrineDbalAdapter $adapter): void
    {
        $this->assertSame(50, $adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $adapter = $this->createAdapterToTestGetSlice();

        $this->doTestGetSlice($adapter);
    }

    public function testGetSliceShouldWorkAfterCallingGetNbResults(): void
    {
        $adapter = $this->createAdapterToTestGetSlice();

        $adapter->getNbResults();

        $this->doTestGetSlice($adapter);
    }

    private function createAdapterToTestGetSlice()
    {
        $countQueryBuilderModifier = function (): void { };

        return new DoctrineDbalAdapter($this->qb, $countQueryBuilderModifier);
    }

    private function doTestGetSlice(DoctrineDbalAdapter $adapter): void
    {
        $offset = 30;
        $length = 10;

        $qb = clone $this->qb;
        $qb->setFirstResult($offset)->setMaxResults($length);

        $expectedResults = $qb->execute()->fetchAll();
        $results = $adapter->getSlice($offset, $length);

        $this->assertSame($expectedResults, $results);
    }

    public function testItShouldThrowAnInvalidArgumentExceptionIfTheQueryIsNotSelect(): void
    {
        $this->expectException(\Pagerfanta\Exception\InvalidArgumentException::class);

        $this->qb->delete('posts');
        $countQueryModifier = function (): void { };

        new DoctrineDbalAdapter($this->qb, $countQueryModifier);
    }

    public function testItShouldCloneTheQuery(): void
    {
        $adapter = $this->createAdapterToTestGetNbResults();

        $this->qb->innerJoin('p', 'comments', 'c', 'c.post_id = p.id')
                ->groupBy('c.post_id');

        $this->assertSame(50, $adapter->getNbResults());
    }

    public function testItShouldThrowAnInvalidArgumentExceptionIfTheCountQueryBuilderModifierIsNotACallable(): void
    {
        $this->expectException(\Pagerfanta\Exception\InvalidArgumentException::class);

        $countQueryBuilderModifier = 'ups';

        new DoctrineDbalAdapter($this->qb, $countQueryBuilderModifier);
    }

    private function createAdapterToTestGetNbResults()
    {
        $countQueryBuilderModifier = function (QueryBuilder $queryBuilder): void {
            $queryBuilder->select('COUNT(DISTINCT p.id) AS total_results')
                         ->setMaxResults(1);
        };

        return new DoctrineDbalAdapter($this->qb, $countQueryBuilderModifier);
    }
}
