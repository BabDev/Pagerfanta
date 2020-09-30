<?php declare(strict_types=1);

$libDir = dirname(__DIR__) . '/lib';
$coreDir = $libDir . '/Core';

// Only packages dependent on pagerfanta/core
$packages = [
    'Adapter/Doctrine/Collections',
    'Adapter/Doctrine/DBAL',
    'Adapter/Doctrine/MongoDBODM',
    'Adapter/Doctrine/ORM',
    'Adapter/Doctrine/PHPCRODM',
    'Adapter/Elastica',
    'Adapter/Solarium',
    'Twig',
];

foreach ($packages as $package) {
    $composerFile = $libDir . '/' . $package . '/composer.json';

    $composerManifest = json_decode(file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
    $composerManifest['repositories'] = [
        (object) [
            'type' => 'path',
            'url' => $coreDir,
        ]
    ];
    $composerManifest['require']['pagerfanta/core'] = '*';

    file_put_contents($composerFile, json_encode($composerManifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
