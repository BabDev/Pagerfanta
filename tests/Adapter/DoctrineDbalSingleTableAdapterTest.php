<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalSingleTableAdapter;
use Pagerfanta\Exception\InvalidArgumentException;

class DoctrineDbalSingleTableAdapterTest extends DoctrineDbalTestCase
{
    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var DoctrineDbalSingleTableAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->qb = new QueryBuilder($this->connection);
        $this->qb->select('p.*')->from('posts', 'p');

        $this->adapter = new DoctrineDbalSingleTableAdapter($this->qb, 'p.id');
    }

    public function testACountFieldWithoutAnAliasIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The $countField must contain a table alias in the string.');

        new DoctrineDbalSingleTableAdapter($this->qb, 'id');
    }

    public function testAQueryWithJoinStatementsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The query builder cannot have joins.');

        $this->qb->innerJoin('p', 'comments', 'c', 'c.post_id = p.id');

        new DoctrineDbalSingleTableAdapter($this->qb, 'p.id');
    }

    public function testAdapterReturnsNumberOfResults(): void
    {
        $this->assertSame(50, $this->adapter->getNbResults());
    }

    public function testResultCountStaysConsistentAfterSlicing(): void
    {
        $this->adapter->getSlice(1, 10);

        $this->assertSame(50, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 30;
        $length = 10;

        $q = clone $this->qb;
        $q->setFirstResult($offset)
            ->setMaxResults($length);

        $this->assertSame($q->execute()->fetchAll(), $this->adapter->getSlice($offset, $length));
    }
}
