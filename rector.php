<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\NarrowUnusedSetUpDefinedPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/lib',
    ])
    ->withSkip([
        /*
         * Skip selected rules in selected files
         */

        NarrowUnusedSetUpDefinedPropertyRector::class => [
            __DIR__.'/lib/Twig/Tests/View/TwigViewIntegrationTest.php', // Tries to inline the route generator factory which is used in the mocked runtime loader
        ],
    ])
    ->withImportNames(importShortClasses: false)
    ->withPHPStanConfigs([__DIR__.'/phpstan.neon'])
    ->withPreparedSets(codeQuality: true, phpunitCodeQuality: true)
;
