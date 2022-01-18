<?php declare(strict_types=1);

namespace Pagerfanta\Elastica\Tests;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use Pagerfanta\Elastica\ElasticaAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticaAdapterTest extends TestCase
{
    /**
     * @var MockObject&Query
     */
    private $query;

    /**
     * @var MockObject&ResultSet
     */
    private $resultSet;

    /**
     * @var MockObject&SearchableInterface
     */
    private $searchable;

    /**
     * @var array<string, string>
     */
    private $options;

    /**
     * @var ElasticaAdapter<mixed>
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->query = $this->createMock(Query::class);
        $this->resultSet = $this->createMock(ResultSet::class);
        $this->searchable = $this->createMock(SearchableInterface::class);

        $this->options = ['option1' => 'value1', 'option2' => 'value2'];

        $this->adapter = new ElasticaAdapter($this->searchable, $this->query, $this->options);
    }

    public function testGetResultSet(): void
    {
        $this->assertNull($this->adapter->getResultSet());

        $this->searchable->expects($this->any())
            ->method('search')
            ->with($this->query, ['from' => 0, 'size' => 1, 'option1' => 'value1', 'option2' => 'value2'])
            ->willReturn($this->resultSet);

        $this->adapter->getSlice(0, 1);

        $this->assertSame($this->resultSet, $this->adapter->getResultSet());
    }

    public function testGetSlice(): void
    {
        $this->searchable->expects($this->any())
            ->method('search')
            ->with($this->query, ['from' => 10, 'size' => 30, 'option1' => 'value1', 'option2' => 'value2'])
            ->willReturn($this->resultSet);

        $resultSet = $this->adapter->getSlice(10, 30);

        $this->assertSame($this->resultSet, $resultSet);
        $this->assertSame($this->resultSet, $this->adapter->getResultSet());
    }

    /**
     * Returns the number of results before search, use count() method if resultSet is empty.
     */
    public function testGetNbResultsBeforeSearch(): void
    {
        $this->searchable->expects($this->once())
            ->method('count')
            ->with($this->query)
            ->willReturn(100);

        $this->assertSame(100, $this->adapter->getNbResults());
    }

    /**
     * Returns the number of results after search, use getTotalHits() method if resultSet is not empty.
     */
    public function testGetNbResultsAfterSearch(): void
    {
        $adapter = new ElasticaAdapter($this->searchable, $this->query, [], 30);

        $this->searchable->expects($this->once())
            ->method('search')
            ->with($this->query, ['from' => 10, 'size' => 30])
            ->willReturn($this->resultSet);

        $this->resultSet->expects($this->once())
            ->method('getTotalHits')
            ->willReturn(100);

        $adapter->getSlice(10, 30);

        $this->assertSame(30, $adapter->getNbResults());
    }

    public function testGetNbResultsWithMaxResultsSet(): void
    {
        $adapter = new ElasticaAdapter($this->searchable, $this->query, [], 10);

        $this->searchable->expects($this->once())
            ->method('count')
            ->with($this->query)
            ->willReturn(100);

        $this->assertSame(10, $adapter->getNbResults());
    }
}
