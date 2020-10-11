<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

DG\BypassFinals::setWhitelist(
    [
        '*/Doctrine/ODM/MongoDB/*',
    ]
);

DG\BypassFinals::enable();
