<?php declare(strict_types=1);

$loader = require __DIR__.'/../vendor/autoload.php';

// fix for bad solarium autoloader in Solarium2: "Solarium" instead of "Solarium_"
$prefixes = $loader->getPrefixes();
if (isset($prefixes['Solarium'])) {
    $loader->add('Solarium_', $prefixes['Solarium']);
    $loader->set('Solarium', []);
}

DG\BypassFinals::enable();
