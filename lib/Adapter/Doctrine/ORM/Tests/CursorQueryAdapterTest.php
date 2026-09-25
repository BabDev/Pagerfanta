<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests;

use Doctrine\ORM\Tools\Pagination\CursorPaginator;
use Doctrine\ORM\Tools\SchemaTool;
use Pagerfanta\CountableCursorPagerfanta;
use Pagerfanta\Cursor\Base64JsonCursorEncoder;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\CursorPagerfantaFactory;
use Pagerfanta\CursorPagerInterface;
use Pagerfanta\Doctrine\ORM\CountableCursorQueryAdapter;
use Pagerfanta\Doctrine\ORM\CursorQueryAdapter;
use Pagerfanta\Doctrine\ORM\Tests\Entity\Group;
use Pagerfanta\Doctrine\ORM\Tests\Entity\Post;
use Pagerfanta\Doctrine\ORM\Tests\Entity\User;
use Pagerfanta\Position\CursorPosition;

final class CursorQueryAdapterTest extends ORMTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(CursorPaginator::class)) {
            $this->markTestSkipped('Cursor pagination requires doctrine/orm 3.7 or later.');
        }

        parent::setUp();

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema([
            $this->entityManager->getClassMetadata(Group::class),
            $this->entityManager->getClassMetadata(Post::class),
            $this->entityManager->getClassMetadata(User::class),
        ]);
    }

    /**
     * Creates posts with the given scores, published a day apart in the order given.
     */
    private function createPosts(int ...$scores): void
    {
        foreach ($scores as $index => $score) {
            $this->entityManager->persist(new Post($score, new \DateTimeImmutable(\sprintf('2026-01-01 +%d days', $index))));
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param iterable<Post> $posts
     *
     * @return list<int|null>
     */
    private function ids(iterable $posts): array
    {
        $ids = [];

        foreach ($posts as $post) {
            $ids[] = $post->id;
        }

        return $ids;
    }

    /**
     * @param CursorPagerInterface<Post> $pager
     *
     * @return array{forward: list<list<int|null>>, backward: list<list<int|null>>}
     */
    private function walk(CursorPagerInterface $pager): array
    {
        \assert($pager instanceof CursorPagerfanta || $pager instanceof CountableCursorPagerfanta);

        $encoder = new Base64JsonCursorEncoder();

        $forward = [$this->ids($pager->getCurrentPageResults())];

        while ($pager->hasNextPage()) {
            $pager = $pager->withPosition(new CursorPosition($encoder->decode($encoder->encode($pager->getNextPosition()->cursor))));
            $forward[] = $this->ids($pager->getCurrentPageResults());
        }

        $backward = [];

        while ($pager->hasPreviousPage()) {
            $pager = $pager->withPosition(new CursorPosition($encoder->decode($encoder->encode($pager->getPreviousPosition()->cursor))));
            $backward[] = $this->ids($pager->getCurrentPageResults());
        }

        return ['forward' => $forward, 'backward' => $backward];
    }

    public function testTheAdapterSupportsBackwardNavigation(): void
    {
        $this->assertTrue((new CursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.id ASC')))->supportsBackwardNavigation());
    }

    public function testTheFirstPageIsReturnedWithoutACursor(): void
    {
        $this->createPosts(10, 20, 30, 40, 50);

        $adapter = new CursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.id ASC'), false);

        $slice = $adapter->getSlice(null, 2);

        $this->assertSame([1, 2], $this->ids($slice->items));
        $this->assertNull($slice->previous);
        $this->assertEquals(new Cursor(['p.id' => 2], Direction::Next), $slice->next);
    }

    public function testThePagesAreFetchedRelativeToTheCursor(): void
    {
        $this->createPosts(10, 20, 30, 40, 50);

        $adapter = new CursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.id ASC'), false);

        $next = $adapter->getSlice(new Cursor(['p.id' => 2]), 2);

        $this->assertSame([3, 4], $this->ids($next->items));
        $this->assertEquals(new Cursor(['p.id' => 3], Direction::Previous), $next->previous);
        $this->assertEquals(new Cursor(['p.id' => 4], Direction::Next), $next->next);

        $previous = $adapter->getSlice(new Cursor(['p.id' => 5], Direction::Previous), 2);

        $this->assertSame([3, 4], $this->ids($previous->items), 'Items are returned in the query order');
        $this->assertEquals(new Cursor(['p.id' => 3], Direction::Previous), $previous->previous);
        $this->assertEquals(new Cursor(['p.id' => 4], Direction::Next), $previous->next);
    }

    public function testThePagesCanBeWalkedForwardAndBackward(): void
    {
        $this->createPosts(10, 20, 30, 40, 50, 60, 70);

        $pager = new CursorPagerfanta(new CursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.id ASC'), false), 3);

        $this->assertSame(
            [
                'forward' => [[1, 2, 3], [4, 5, 6], [7]],
                'backward' => [[4, 5, 6], [1, 2, 3]],
            ],
            $this->walk($pager),
        );
    }

    public function testAnExactMultipleOfTheLimitHasNoPhantomNextPage(): void
    {
        $this->createPosts(10, 20, 30, 40, 50, 60);

        $pager = new CursorPagerfanta(new CursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.id ASC'), false), 3);

        $this->assertSame([[1, 2, 3], [4, 5, 6]], $this->walk($pager)['forward']);
    }

    public function testAnEmptyResultSetHasNoPages(): void
    {
        $pager = new CursorPagerfanta(new CursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.id ASC'), false), 3);

        $this->assertSame([], $pager->getCurrentPageResults());
        $this->assertFalse($pager->haveToPaginate());
        $this->assertFalse($pager->hasPreviousPage());
        $this->assertFalse($pager->hasNextPage());
    }

    public function testTiesOnTheLeadingSortFieldAreBrokenByTheFollowingFields(): void
    {
        // Sorted by score descending then ID ascending: 2 (90), 1 (80), 3 (80), 5 (80), 6 (80), 4 (70)
        $this->createPosts(80, 90, 80, 70, 80, 80);

        $query = $this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.score DESC, p.id ASC');

        $pager = new CursorPagerfanta(new CursorQueryAdapter($query, false), 2);

        $this->assertEquals(new Cursor(['p.score' => 80, 'p.id' => 1]), $pager->getNextPosition()->cursor);

        $this->assertSame(
            [
                'forward' => [[2, 1], [3, 5], [6, 4]],
                'backward' => [[3, 5], [2, 1]],
            ],
            $this->walk($pager),
        );
    }

    public function testADateTimeSortFieldIsConvertedToADatabaseValue(): void
    {
        $this->createPosts(10, 20, 30, 40, 50);

        $pager = new CursorPagerfanta(new CursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.publishedAt DESC, p.id DESC'), false), 2);

        $this->assertEquals(new Cursor(['p.publishedAt' => '2026-01-04 00:00:00', 'p.id' => 4]), $pager->getNextPosition()->cursor);

        $this->assertSame(
            [
                'forward' => [[5, 4], [3, 2], [1]],
                'backward' => [[3, 2], [5, 4]],
            ],
            $this->walk($pager),
        );
    }

    public function testTheQueryParametersAreKept(): void
    {
        $this->createPosts(10, 20, 30, 40, 50, 60, 70);

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Post::class, 'p')
            ->where('p.score > :score')
            ->orderBy('p.id', 'ASC')
            ->setParameter('score', 20);

        $pager = new CursorPagerfanta(new CursorQueryAdapter($queryBuilder, false), 2);

        $this->assertSame(
            [
                'forward' => [[3, 4], [5, 6], [7]],
                'backward' => [[5, 6], [3, 4]],
            ],
            $this->walk($pager),
        );
    }

    public function testAQueryJoiningACollectionReturnsEachRootEntityOnce(): void
    {
        $users = [new User(), new User(), new User()];
        $groups = [new Group(), new Group(), new Group()];

        foreach ($users as $user) {
            foreach ($groups as $group) {
                $user->addGroup($group);
                $this->entityManager->persist($group);
            }

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $pager = new CursorPagerfanta(new CursorQueryAdapter($this->entityManager->createQuery('SELECT u, g FROM '.User::class.' u INNER JOIN u.groups g ORDER BY u.id ASC')), 2);

        $ids = static fn (array $users): array => array_map(static fn (User $user): ?int => $user->id, $users);

        $this->assertSame([1, 2], $ids($pager->getCurrentPageResults()));
        $this->assertCount(3, $pager->getCurrentPageResults()[0]->getGroups(), 'The joined collection is fully hydrated');

        $pager = $pager->withPosition($pager->getNextPosition());

        $this->assertSame([3], $ids($pager->getCurrentPageResults()));
        $this->assertFalse($pager->hasNextPage());
    }

    public function testTheCursorAdapterIsNotCountable(): void
    {
        $this->assertInstanceOf(CursorPagerfanta::class, CursorPagerfantaFactory::create(new CursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.id ASC'))));
    }

    public function testTheCountableAdapterCountsAllResultsIgnoringTheCursor(): void
    {
        $this->createPosts(10, 20, 30, 40, 50);

        $pager = CursorPagerfantaFactory::create(new CountableCursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p WHERE p.score > 10 ORDER BY p.id ASC'), false), 2);

        $this->assertInstanceOf(CountableCursorPagerfanta::class, $pager);
        $this->assertSame(4, $pager->getNbResults());

        $pager = $pager->withPosition($pager->getNextPosition());

        $this->assertSame([4, 5], $this->ids($pager->getCurrentPageResults()));
        $this->assertSame(4, $pager->getNbResults());
    }

    public function testTheCountableAdapterCanCountBeforeASliceIsFetched(): void
    {
        $this->createPosts(10, 20, 30);

        $adapter = new CountableCursorQueryAdapter($this->entityManager->createQuery('SELECT p FROM '.Post::class.' p ORDER BY p.id ASC'), false);

        $this->assertSame(3, $adapter->getNbResults());
    }
}
