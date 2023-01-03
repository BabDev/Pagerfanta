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
     * @var MockObject&Builder
     */
    private $queryBuilder;

    /**
     * @var QueryAdapter<mixed>
     */
    private QueryAdapter $adapter;

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

    public function testGetNbResultsShouldCreateTheQueryAndCount(): void
    {
        /** @var MockObject&Query $query */
        $query = $this->createMock(Query::class);

        $query->expects(self::once())
            ->method('execute')
            ->willReturn(110);

        $this->queryBuilder->expects(self::once())
            ->method('limit')
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('skip')
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('count')
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('getQuery')
            ->willReturn($query);

        self::assertSame(110, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 10;
        $length = 15;
        $slice = new \ArrayIterator();

        /** @var MockObject&Query $query */
        $query = $this->createMock(Query::class);
        $query->expects(self::once())
            ->method('execute')
            ->willReturn($slice);

        $this->queryBuilder->expects(self::once())
            ->method('limit')
            ->with($length)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('skip')
            ->with($offset)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('getQuery')
            ->willReturn($query);

        self::assertSame($slice, $this->adapter->getSlice($offset, $length));
    }
}
