<?php declare(strict_types=1);

use DG\BypassFinals;

require __DIR__.'/../vendor/autoload.php';

BypassFinals::allowPaths([
    '*/vendor/doctrine/mongodb-odm/lib/Doctrine/ODM/MongoDB/*', // doctrine/mongodb < 2.14
    '*/vendor/doctrine/mongodb-odm/src/*', // doctrine/mongodb >= 2.14
    '*/vendor/doctrine/phpcr-odm/lib/Doctrine/ODM/PHPCR/*',
]);

BypassFinals::enable(bypassReadOnly: false);
