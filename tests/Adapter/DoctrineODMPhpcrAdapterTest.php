<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use Pagerfanta\Adapter\DoctrineODMPhpcrAdapter;
use PHPCR\Query\QueryResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoctrineODMPhpcrAdapterTest extends TestCase
{
    /**
     * @var MockObject|QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var MockObject|Query
     */
    private $query;

    /**
     * @var DoctrineODMPhpcrAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(Query::class);

        $this->adapter = new DoctrineODMPhpcrAdapter($this->queryBuilder);
    }

    public function testGetQueryBuilder(): void
    {
        $this->assertSame($this->queryBuilder, $this->adapter->getQueryBuilder());
    }

    public function testGetNbResultsShouldCreateTheQueryAndCount(): void
    {
        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);

        $queryResult = $this->createMock(QueryResultInterface::class);
        $queryResult->expects($this->once())
            ->method('getRows')
            ->willReturn(new \ArrayIterator([1, 2, 3, 4, 5, 6]));

        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn($queryResult);

        $this->assertSame(6, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 10;
        $length = 15;
        $slice = new \ArrayIterator();

        $this->query->expects($this->once())
            ->method('setMaxResults')
            ->with($length)
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('setFirstResult')
            ->with($offset)
            ->willReturn($this->query);

        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn($slice);

        $this->assertSame($slice, $this->adapter->getSlice($offset, $length));
    }
}
