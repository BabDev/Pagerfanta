<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Doctrine\ORM\Tests\Entity\Group;
use Pagerfanta\Doctrine\ORM\Tests\Entity\Person;
use Pagerfanta\Doctrine\ORM\Tests\Entity\User;

final class QueryAdapterTest extends ORMTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema([
            $this->entityManager->getClassMetadata(Group::class),
            $this->entityManager->getClassMetadata(Person::class),
            $this->entityManager->getClassMetadata(User::class),
        ]);

        $user1 = new User();
        $user2 = new User();

        $group1 = new Group();
        $group2 = new Group();
        $group3 = new Group();

        $user1->addGroup($group1);
        $user1->addGroup($group2);
        $user1->addGroup($group3);

        $user2->addGroup($group1);

        $person1 = new Person();
        $person1->name = 'Foo';
        $person1->biography = 'Baz bar';

        $person2 = new Person();
        $person2->name = 'Bar';
        $person2->biography = 'Bar baz';

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);

        $this->entityManager->persist($group1);
        $this->entityManager->persist($group2);
        $this->entityManager->persist($group3);

        $this->entityManager->persist($person1);
        $this->entityManager->persist($person2);

        $this->entityManager->flush();
    }

    public function testAdapterReturnsNumberOfResultsForSingleTableQuery(): void
    {
        $adapter = new QueryAdapter($this->entityManager->createQuery('SELECT u FROM '.User::class.' u'));

        self::assertSame(2, $adapter->getNbResults());
    }

    public function testAdapterReturnsNumberOfResultsForAJoinedCollection(): void
    {
        $adapter = new QueryAdapter($this->entityManager->createQuery('SELECT u, g FROM '.User::class.' u INNER JOIN u.groups g'));

        self::assertSame(2, $adapter->getNbResults());
    }

    public static function dataGetSlice(): \Generator
    {
        yield '0 offset, 1 item' => [0, 1, 1];
        yield '0 offset, 10 items' => [0, 10, 2];
        yield '1 offset, 1 item' => [1, 1, 1];
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @dataProvider dataGetSlice
     */
    public function testCurrentPageSliceForSingleTableQuery(int $offset, int $length, int $expectedCount): void
    {
        $adapter = new QueryAdapter($this->entityManager->createQuery('SELECT u FROM '.User::class.' u'));

        self::assertCount($expectedCount, $adapter->getSlice($offset, $length));
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @dataProvider dataGetSlice
     */
    public function testCurrentPageSliceForAJoinedCollection(int $offset, int $length, int $expectedCount): void
    {
        $adapter = new QueryAdapter($this->entityManager->createQuery('SELECT u, g FROM '.User::class.' u INNER JOIN u.groups g'));

        self::assertCount($expectedCount, $adapter->getSlice($offset, $length));
    }

    public function testResultCountStaysConsistentAfterSlicing(): void
    {
        $adapter = new QueryAdapter($this->entityManager->createQuery('SELECT u FROM '.User::class.' u'));
        $results = $adapter->getNbResults();

        $adapter->getSlice(0, 1);

        self::assertSame($results, $adapter->getNbResults());
    }

    public function testResultSetIsSlicedWhenSelectingEntitiesAndSingleFields(): void
    {
        $adapter = new QueryAdapter($this->entityManager->createQuery('SELECT p, p.name FROM '.Person::class.' p'));

        self::assertSame(2, $adapter->getNbResults());

        $items = $adapter->getSlice(0, 10);

        self::assertCount(2, $items);
        self::assertArrayHasKey('name', $items[0]);
    }

    public function testResultSetIsLoadedWithCaseInSelectStatement(): void
    {
        $dql = <<<DQL
            SELECT p,
            CASE
              WHEN p.name LIKE :keyword AND p.biography LIKE :keyword THEN 0
              WHEN p.name LIKE :keyword THEN 1
              WHEN p.biography LIKE :keyword THEN 2
              ELSE 3
            END AS relevance

            FROM Pagerfanta\Doctrine\ORM\Tests\Entity\Person p
            WHERE (
                 p.name LIKE :keyword
              OR p.biography LIKE :keyword
            )
            GROUP BY p.id
            ORDER BY relevance ASC, p.id DESC
            DQL
        ;

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('keyword', '%Foo%');

        $adapter = new QueryAdapter($query);

        self::assertSame(1, $adapter->getNbResults());

        $items = $adapter->getSlice(0, 10);

        self::assertSame('Foo', $items[0][0]->name);

        if (\PHP_VERSION_ID >= 80100) {
            self::assertSame(1, $items[0]['relevance']);
        } else {
            self::assertSame('1', $items[0]['relevance']);
        }
    }

    public function testAQueryBuilderIsAccepted(): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        $adapter = new QueryAdapter($queryBuilder);

        self::assertSame(2, $adapter->getNbResults());
        self::assertCount(2, $adapter->getSlice(0, 10));
    }
}
