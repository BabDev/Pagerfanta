<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL\Tests;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Exception\InvalidArgumentException;

final class QueryAdapterTest extends DBALTestCase
{
    /**
     * @var QueryBuilder
     */
    private $qb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->qb = new QueryBuilder($this->connection);
        $this->qb->select('p.*')->from('posts', 'p');
    }

    public function testANonSelectQueryIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only SELECT queries can be paginated.');

        $this->qb->delete('posts');

        new QueryAdapter($this->qb, static function (QueryBuilder $qb): void { });
    }

    public function testAdapterReturnsNumberOfResults(): void
    {
        $adapter = $this->createAdapterToTestGetNbResults();

        $this->assertSame(50, $adapter->getNbResults());
    }

    public function testResultCountStaysConsistentAfterSlicing(): void
    {
        $adapter = $this->createAdapterToTestGetNbResults();

        $adapter->getSlice(1, 10);

        $this->assertSame(50, $adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $adapter = new QueryAdapter($this->qb, static function (QueryBuilder $qb): void { });

        $offset = 30;
        $length = 10;

        $this->qb->setFirstResult($offset)
            ->setMaxResults($length);

        $this->assertSame($this->qb->execute()->fetchAll(), $adapter->getSlice($offset, $length));
    }

    public function testTheAdapterUsesAClonedQuery(): void
    {
        $adapter = $this->createAdapterToTestGetNbResults();

        $this->qb->innerJoin('p', 'comments', 'c', 'c.post_id = p.id')
            ->groupBy('c.post_id');

        $this->assertSame(50, $adapter->getNbResults());
    }

    private function createAdapterToTestGetNbResults(): QueryAdapter
    {
        return new QueryAdapter(
            $this->qb,
            static function (QueryBuilder $qb): void {
                $qb->select('COUNT(DISTINCT p.id) AS total_results')
                    ->setMaxResults(1);
            }
        );
    }
}
