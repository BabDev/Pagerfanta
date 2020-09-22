<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Exception\InvalidArgumentException;

class DoctrineDbalAdapterTest extends DoctrineDbalTestCase
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

        new DoctrineDbalAdapter($this->qb, static function (QueryBuilder $qb): void { });
    }

    public function testTheConstructorRejectsTheQueryBuilderModifierIfItIsNotCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The $countQueryBuilderModifier argument of the %s constructor must be a callable, a string was given.', QueryAdapter::class));

        new DoctrineDbalAdapter($this->qb, 'ups');
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
        $adapter = new DoctrineDbalAdapter($this->qb, static function (QueryBuilder $qb): void { });

        $offset = 30;
        $length = 10;

        $this->qb->setFirstResult($offset)
            ->setMaxResults($length);

        $stmt = $this->qb->execute();

        $fetcher = method_exists($stmt, 'fetchAllAssociative') ? 'fetchAllAssociative' : 'fetchAll';

        $this->assertSame($stmt->$fetcher(), $adapter->getSlice($offset, $length));
    }

    public function testTheAdapterUsesAClonedQuery(): void
    {
        $adapter = $this->createAdapterToTestGetNbResults();

        $this->qb->innerJoin('p', 'comments', 'c', 'c.post_id = p.id')
                ->groupBy('c.post_id');

        $this->assertSame(50, $adapter->getNbResults());
    }

    private function createAdapterToTestGetNbResults(): DoctrineDbalAdapter
    {
        return new DoctrineDbalAdapter(
            $this->qb,
            static function (QueryBuilder $qb): void {
                $qb->select('COUNT(DISTINCT p.id) AS total_results')
                    ->setMaxResults(1);
            }
        );
    }
}
