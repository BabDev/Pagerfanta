<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests;

use Doctrine\DBAL\DriverManager;
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
        $config->setMetadataCache(new ArrayAdapter());
        $config->setQueryCache(new ArrayAdapter());
        $config->setResultCache(new ArrayAdapter());
        $config->setMetadataDriverImpl(new AttributeDriver([__DIR__.'/Entity']));

        /** @phpstan-ignore-next-line function.alreadyNarrowedType */
        if (\PHP_VERSION_ID >= 80400 && method_exists($config, 'enableNativeLazyObjects')) {
            $config->enableNativeLazyObjects(true);
        } else {
            $config->setProxyDir(__DIR__.'/_files');
            $config->setProxyNamespace(__NAMESPACE__.'\Proxies');
        }

        $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $this->entityManager = new EntityManager(DriverManager::getConnection($conn, $config), $config);
    }
}
