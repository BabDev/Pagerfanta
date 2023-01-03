<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

abstract class ORMTestCase extends TestCase
{
    protected EntityManager $entityManager;

    protected function setUp(): void
    {
        $config = new Configuration();

        if (method_exists($config, 'setMetadataCache')) {
            $config->setMetadataCache(new ArrayAdapter());
        } else {
            $config->setMetadataCacheImpl(DoctrineProvider::wrap(new ArrayAdapter()));
        }

        if (method_exists($config, 'setQueryCache')) {
            $config->setQueryCache(new ArrayAdapter());
        } else {
            $config->setQueryCacheImpl(DoctrineProvider::wrap(new ArrayAdapter()));
        }

        if (method_exists($config, 'setResultCache')) {
            $config->setResultCache(new ArrayAdapter());
        } else {
            $config->setResultCacheImpl(DoctrineProvider::wrap(new ArrayAdapter()));
        }

        $config->setProxyDir(__DIR__.'/_files');
        $config->setProxyNamespace(__NAMESPACE__.'\Proxies');

        if (\PHP_VERSION_ID >= 80000 && class_exists(AttributeDriver::class)) {
            $config->setMetadataDriverImpl(new AttributeDriver([__DIR__.'/Entity']));
        } else {
            $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver([__DIR__.'/Entity'], false));
        }

        $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $this->entityManager = EntityManager::create($conn, $config);
    }
}
