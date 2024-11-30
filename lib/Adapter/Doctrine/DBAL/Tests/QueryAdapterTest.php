<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL\Tests;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use PHPUnit\Framework\Attributes\Group;

final class QueryAdapterTest extends DBALTestCase
{
    private QueryBuilder $qb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->qb = $this->connection->createQueryBuilder();
        $this->qb->select('p.*')->from('posts', 'p');
    }

    #[Group('legacy')]
    public function testAdapterReturnsNumberOfResultsWhenCallbackUsesDeprecatedNoReturnSignature(): void
    {
        $adapter = new QueryAdapter(
            $this->qb,
            static function (QueryBuilder $qb): void {
                $qb->select('COUNT(DISTINCT p.id) AS total_results')
                    ->setMaxResults(1);
            }
        );

        $this->assertSame(50, $adapter->getNbResults());
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
        $adapter = new QueryAdapter($this->qb, static fn (QueryBuilder $qb): QueryBuilder => $qb);

        $offset = 30;
        $length = 10;

        $this->qb->setFirstResult($offset)
            ->setMaxResults($length);

        $this->assertSame($this->qb->executeQuery()->fetchAllAssociative(), $adapter->getSlice($offset, $length));
    }

    public function testTheAdapterUsesAClonedQuery(): void
    {
        $adapter = $this->createAdapterToTestGetNbResults();

        $this->qb->innerJoin('p', 'comments', 'c', 'c.post_id = p.id')
            ->groupBy('c.post_id');

        $this->assertSame(50, $adapter->getNbResults());
    }

    /**
     * @return QueryAdapter<mixed>
     */
    private function createAdapterToTestGetNbResults(): QueryAdapter
    {
        return new QueryAdapter(
            $this->qb,
            static fn (QueryBuilder $qb): QueryBuilder => $qb->select('COUNT(DISTINCT p.id) AS total_results')->setMaxResults(1),
        );
    }
}
