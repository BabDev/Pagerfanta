<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoctrineSelectableAdapterTest extends TestCase
{
    /**
     * @var MockObject|Selectable
     */
    private $selectable;

    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @var DoctrineSelectableAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->selectable = $this->createMock(Selectable::class);
        $this->criteria = $this->createCriteria();

        $this->adapter = new DoctrineSelectableAdapter($this->selectable, $this->criteria);
    }

    private function createCriteria(): Criteria
    {
        $criteria = new Criteria();
        $criteria->orderBy(['username' => 'ASC']);
        $criteria->setFirstResult(2);
        $criteria->setMaxResults(3);

        return $criteria;
    }

    public function testGetNbResults(): void
    {
        $this->criteria->setFirstResult(null);
        $this->criteria->setMaxResults(null);

        $collection = $this->createMock(Collection::class);
        $collection
            ->expects($this->any())
            ->method('count')
            ->willReturn(10);

        $this->selectable
            ->expects($this->once())
            ->method('matching')
            ->with($this->equalTo($this->criteria))
            ->willReturn($collection);

        $this->assertSame(10, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $this->criteria->setFirstResult(10);
        $this->criteria->setMaxResults(20);

        $slice = new \stdClass();

        $this->selectable
            ->expects($this->once())
            ->method('matching')
            ->with($this->equalTo($this->criteria))
            ->willReturn($slice);

        $this->assertSame($slice, $this->adapter->getSlice(10, 20));
    }
}
