<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

abstract class DoctrineORMTestCase extends TestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected function setUp(): void
    {
        $config = new Configuration();

        if (method_exists($config, 'setMetadataCache')) {
            $config->setMetadataCache(new ArrayAdapter());
        } else {
            $config->setMetadataCacheImpl(DoctrineProvider::wrap(new ArrayAdapter()));
        }

        $config->setQueryCacheImpl(DoctrineProvider::wrap(new ArrayAdapter()));
        $config->setResultCacheImpl(DoctrineProvider::wrap(new ArrayAdapter()));
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
