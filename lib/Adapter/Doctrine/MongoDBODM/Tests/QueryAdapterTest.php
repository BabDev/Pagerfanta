<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\MongoDBODM\Tests;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Pagerfanta\Doctrine\MongoDBODM\QueryAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class QueryAdapterTest extends TestCase
{
    /**
     * @var MockObject|Builder
     */
    private $queryBuilder;

    /**
     * @var QueryAdapter
     */
    private $adapter;

    public static function setUpBeforeClass(): void
    {
        if (!class_exists(DocumentManager::class)) {
            self::markTestSkipped('doctrine/mongodb-odm is not installed');
        }
    }

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(Builder::class);

        $this->adapter = new QueryAdapter($this->queryBuilder);
    }

    public function testGetQueryBuilder(): void
    {
        $this->assertSame($this->queryBuilder, $this->adapter->getQueryBuilder());
    }

    public function testGetNbResultsShouldCreateTheQueryAndCount(): void
    {
        $query = $this->createMock(Query::class);

        $query->expects($this->once())
            ->method('execute')
            ->willReturn(110);

        $this->queryBuilder->expects($this->once())
            ->method('limit')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('skip')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('count')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->assertSame(110, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 10;
        $length = 15;
        $slice = new \ArrayIterator();

        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('execute')
            ->willReturn($slice);

        $this->queryBuilder->expects($this->once())
            ->method('limit')
            ->with($length)
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('skip')
            ->with($offset)
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->assertSame($slice, $this->adapter->getSlice($offset, $length));
    }
}
