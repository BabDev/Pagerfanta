<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

abstract class DBALTestCase extends TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    protected function setUp(): void
    {
        $this->connection = $this->createConnection();

        $this->createSchema();
        $this->insertData();
    }

    private function createConnection(): Connection
    {
        return DriverManager::getConnection(
            [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ]
        );
    }

    private function createSchema(): void
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

        $queries = $schema->toSql($this->connection->getDatabasePlatform()); // get queries to create this schema.

        foreach ($queries as $sql) {
            $this->connection->executeQuery($sql);
        }
    }

    private function insertData(): void
    {
        $this->connection->transactional(
            static function (Connection $connection): void {
                for ($i = 1; $i <= 50; ++$i) {
                    $connection->insert('posts', ['username' => 'Jon Doe', 'post_content' => 'Post #'.$i]);

                    for ($j = 1; $j <= 5; ++$j) {
                        $connection->insert('comments', ['post_id' => $i, 'username' => 'Jon Doe', 'content' => 'Comment #'.$j]);
                    }
                }
            }
        );
    }
}
