<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL\Tests;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Doctrine\DBAL\SingleTableQueryAdapter;
use Pagerfanta\Exception\InvalidArgumentException;

final class SingleTableQueryAdapterTest extends DBALTestCase
{
    private QueryBuilder $qb;

    /**
     * @var SingleTableQueryAdapter<mixed>
     */
    private SingleTableQueryAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->qb = new QueryBuilder($this->connection);
        $this->qb->select('p.*')->from('posts', 'p');

        $this->adapter = new SingleTableQueryAdapter($this->qb, 'p.id');
    }

    public function testACountFieldWithoutAnAliasIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The $countField must contain a table alias in the string.');

        new SingleTableQueryAdapter($this->qb, 'id');
    }

    public function testAQueryWithJoinStatementsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The query builder cannot have joins.');

        $this->qb->innerJoin('p', 'comments', 'c', 'c.post_id = p.id');

        new SingleTableQueryAdapter($this->qb, 'p.id');
    }

    public function testAdapterReturnsNumberOfResults(): void
    {
        self::assertSame(50, $this->adapter->getNbResults());
    }

    public function testResultCountStaysConsistentAfterSlicing(): void
    {
        $this->adapter->getSlice(1, 10);

        self::assertSame(50, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 30;
        $length = 10;

        $q = clone $this->qb;
        $q->setFirstResult($offset)
            ->setMaxResults($length);

        self::assertSame($q->executeQuery()->fetchAllAssociative(), $this->adapter->getSlice($offset, $length));
    }
}
