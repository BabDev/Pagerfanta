<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter\DoctrineORM;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

abstract class DoctrineORMTestCase extends TestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected function setUp(): void
    {
        $config = new Configuration();
        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setProxyDir(__DIR__.'/_files');
        $config->setProxyNamespace(__NAMESPACE__.'\Proxies');
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver());

        $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $this->entityManager = EntityManager::create($conn, $config);
    }
}

/**
 * @Entity
 */
class MyBlogPost
{
    /** @Id @column(type="integer") @generatedValue */
    public $id;
    /**
     * @ManyToOne(targetEntity="Author")
     */
    public $author;
    /**
     * @ManyToOne(targetEntity="Category")
     */
    public $category;
}

/**
 * @Entity
 */
class MyAuthor
{
    /** @Id @column(type="integer") @generatedValue */
    public $id;
}

/**
 * @Entity
 */
class MyCategory
{
    /** @id @column(type="integer") @generatedValue */
    public $id;
}

/**
 * @Entity
 */
class BlogPost
{
    /** @Id @column(type="integer") @generatedValue */
    public $id;
    /**
     * @ManyToOne(targetEntity="Author")
     */
    public $author;
    /**
     * @ManyToOne(targetEntity="Category")
     */
    public $category;
}

/**
 * @Entity
 */
class Author
{
    /** @Id @column(type="integer") @generatedValue */
    public $id;
    /** @Column(type="string") */
    public $name;
}

/**
 * @Entity
 */
class Person
{
    /** @Id @column(type="integer") @generatedValue */
    public $id;
    /** @Column(type="string") */
    public $name;
    /** @Column(type="string") */
    public $biography;
}

/**
 * @Entity
 */
class Category
{
    /** @id @column(type="integer") @generatedValue */
    public $id;
}

/** @Entity @Table(name="groups") */
class Group
{
    /** @Id @column(type="integer") @generatedValue */
    public $id;
    /** @ManyToMany(targetEntity="User", mappedBy="groups") */
    public $users;
}

/** @Entity */
class User
{
    /** @Id @column(type="integer") @generatedValue */
    public $id;
    /**
     * @ManyToMany(targetEntity="Group", inversedBy="users")
     * @JoinTable(
     *  name="user_group",
     *  joinColumns = {@JoinColumn(name="user_id", referencedColumnName="id")},
     *  inverseJoinColumns = {@JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    public $groups;
}
