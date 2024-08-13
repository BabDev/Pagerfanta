<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/lib',
    ])
    ->withImportNames(importShortClasses: false)
    ->withPHPStanConfigs([__DIR__.'/phpstan.neon'])
    ->withPreparedSets(codeQuality: true)
    ->withSets([
        DoctrineSetList::DOCTRINE_DBAL_30,
        DoctrineSetList::DOCTRINE_ORM_214,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
;
