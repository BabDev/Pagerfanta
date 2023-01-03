<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\PHPCRODM\Tests;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use Pagerfanta\Doctrine\PHPCRODM\QueryAdapter;
use PHPCR\Query\QueryResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class QueryAdapterTest extends TestCase
{
    /**
     * @var MockObject&QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var MockObject&Query
     */
    private $query;

    /**
     * @var QueryAdapter<mixed>
     */
    private QueryAdapter $adapter;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(Query::class);

        $this->adapter = new QueryAdapter($this->queryBuilder);
    }

    /**
     * @group legacy
     */
    public function testGetQueryBuilder(): void
    {
        self::assertSame($this->queryBuilder, $this->adapter->getQueryBuilder());
    }

    public function testGetNbResultsShouldCreateTheQueryAndCount(): void
    {
        $this->queryBuilder->expects(self::once())
            ->method('getQuery')
            ->willReturn($this->query);

        /** @var MockObject&QueryResultInterface $queryResult */
        $queryResult = $this->createMock(QueryResultInterface::class);
        $queryResult->expects(self::once())
            ->method('getRows')
            ->willReturn(new \ArrayIterator([1, 2, 3, 4, 5, 6]));

        $this->query->expects(self::once())
            ->method('execute')
            ->willReturn($queryResult);

        self::assertSame(6, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 10;
        $length = 15;
        $slice = new \ArrayIterator();

        $this->query->expects(self::once())
            ->method('setMaxResults')
            ->with($length)
            ->willReturn($this->query);

        $this->query->expects(self::once())
            ->method('setFirstResult')
            ->with($offset)
            ->willReturn($this->query);

        $this->queryBuilder->expects(self::once())
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects(self::once())
            ->method('execute')
            ->willReturn($slice);

        self::assertSame($slice, $this->adapter->getSlice($offset, $length));
    }
}
