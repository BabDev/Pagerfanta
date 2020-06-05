<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use PHPUnit\Framework\TestCase;

class DoctrineODMMongoDBAdapterTest extends TestCase
{
    private $queryBuilder;
    private $query;

    /**
     * @var DoctrineODMMongoDBAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        if ($this->isDoctrineMongoNotAvailable()) {
            $this->markTestSkipped('Doctrine MongoDB is not available');
        }

        $this->queryBuilder = $this->createQueryBuilderMock();
        $this->query = $this->createQueryMock();

        $this->adapter = new DoctrineODMMongoDBAdapter($this->queryBuilder);
    }

    private function isDoctrineMongoNotAvailable()
    {
        return !class_exists('Doctrine\ODM\MongoDB\Query\Builder');
    }

    private function createQueryBuilderMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createQueryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ODM\MongoDB\Query\Query')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetQueryBuilder(): void
    {
        $this->assertSame($this->queryBuilder, $this->adapter->getQueryBuilder());
    }

    public function testGetNbResultsShouldCreateTheQueryAndCount(): void
    {
        $this->queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);
        $this->query
            ->expects($this->once())
            ->method('count')
            ->willReturn(110);

        $this->assertSame(110, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 10;
        $length = 15;
        $slice = new \ArrayIterator();

        $this->queryBuilder
            ->expects($this->once())
            ->method('limit')
            ->with($length)
            ->willReturn($this->queryBuilder)
        ;
        $this->queryBuilder
            ->expects($this->once())
            ->method('skip')
            ->with($offset)
            ->willReturn($this->queryBuilder)
        ;
        $this->queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query)
        ;
        $this->query
            ->expects($this->once())
            ->method('execute')
            ->willReturn($slice)
        ;

        $this->assertSame($slice, $this->adapter->getSlice($offset, $length));
    }
}
