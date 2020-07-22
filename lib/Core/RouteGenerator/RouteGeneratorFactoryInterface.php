<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

interface RouteGeneratorFactoryInterface
{
    public function create(array $options = []): RouteGeneratorInterface;
}
