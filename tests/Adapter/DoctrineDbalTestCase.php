<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

abstract class DoctrineDbalTestCase extends TestCase
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    protected function setUp(): void
    {
        $conn = $this->getConnection();

        $this->createSchema($conn);
        $this->insertData($conn);

        $this->qb = new QueryBuilder($conn);
        $this->qb->select('p.*')->from('posts', 'p');
    }

    private function getConnection(): Connection
    {
        $params = $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        return DriverManager::getConnection($params);
    }

    private function createSchema($conn): void
    {
        $schema = new Schema();
        $posts = $schema->createTable('posts');
        $posts->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $posts->addColumn('username', 'string', ['length' => 32]);
        $posts->addColumn('post_content', 'text');
        $posts->setPrimaryKey(['id']);

        $comments = $schema->createTable('comments');
        $comments->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $comments->addColumn('post_id', 'integer', ['unsigned' => true]);
        $comments->addColumn('username', 'string', ['length' => 32]);
        $comments->addColumn('content', 'text');
        $comments->setPrimaryKey(['id']);

        $queries = $schema->toSql($conn->getDatabasePlatform()); // get queries to create this schema.

        foreach ($queries as $sql) {
            $conn->executeQuery($sql);
        }
    }

    private function insertData($conn): void
    {
        for ($i = 1; $i <= 50; ++$i) {
            $conn->insert('posts', ['username' => 'Jon Doe', 'post_content' => 'Post #'.$i]);

            for ($j = 1; $j <= 5; ++$j) {
                $conn->insert('comments', ['post_id' => $i, 'username' => 'Jon Doe', 'content' => 'Comment #'.$j]);
            }
        }
    }
}
