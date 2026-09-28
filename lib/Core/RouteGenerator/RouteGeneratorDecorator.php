<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

/**
 * @deprecated since Pagerfanta 4.10, use {@see PositionRouteGeneratorDecorator} instead
 */
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
        trigger_deprecation('pagerfanta/core', '4.10', 'The "%s" class is deprecated, use "%s" instead.', self::class, PositionRouteGeneratorDecorator::class);

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
