<?php

namespace Pagerfanta\RouteGenerator;

interface RouteGeneratorFactoryInterface
{
    public function create(array $options = []): RouteGeneratorInterface;
}
