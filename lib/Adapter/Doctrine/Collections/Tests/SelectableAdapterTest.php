<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections\Tests;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Pagerfanta\Doctrine\Collections\SelectableAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SelectableAdapterTest extends TestCase
{
    /**
     * @var MockObject&Selectable<array-key, mixed>
     */
    private $selectable;

    private Criteria $criteria;

    /**
     * @var SelectableAdapter<array-key, mixed>
     */
    private SelectableAdapter $adapter;

    protected function setUp(): void
    {
        $this->selectable = $this->createMock(Selectable::class);
        $this->criteria = $this->createCriteria();

        $this->adapter = new SelectableAdapter($this->selectable, $this->criteria);
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

        /** @var MockObject&Collection<array-key, mixed> $collection */
        $collection = $this->createMock(Collection::class);
        $collection->method('count')
            ->willReturn(10);

        $this->selectable->expects(self::once())
            ->method('matching')
            ->with($this->criteria)
            ->willReturn($collection);

        self::assertSame(10, $this->adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $this->criteria->setFirstResult(10);
        $this->criteria->setMaxResults(20);

        $slice = [];

        $this->selectable->expects(self::once())
            ->method('matching')
            ->with($this->criteria)
            ->willReturn($slice);

        self::assertSame($slice, $this->adapter->getSlice(10, 20));
    }
}
