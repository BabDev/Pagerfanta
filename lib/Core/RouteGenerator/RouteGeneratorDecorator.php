<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

final class RouteGeneratorDecorator implements RouteGeneratorInterface
{
    /**
     * @var callable(int): string
     */
    private $decorated;

    /**
     * @param callable(int $page): string $decorated
     */
    public function __construct(callable $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(int $page): string
    {
        return $this->route($page);
    }

    public function route(int $page): string
    {
        $decorated = $this->decorated;

        return $decorated($page);
    }
}
