<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

abstract class ORMTestCase extends TestCase
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
        $config->setResultCacheImpl(new ArrayCache());
        $config->setProxyDir(__DIR__.'/_files');
        $config->setProxyNamespace(__NAMESPACE__.'\Proxies');
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver([__DIR__.'/Entity'], false));

        $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $this->entityManager = EntityManager::create($conn, $config);
    }
}
