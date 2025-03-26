<?php declare(strict_types=1);

use DG\BypassFinals;

require __DIR__.'/../vendor/autoload.php';

BypassFinals::allowPaths([
    '*/Doctrine/ODM/MongoDB/*',
    '*/Doctrine/ODM/PHPCR/*',
]);

BypassFinals::enable(bypassReadOnly: false);
