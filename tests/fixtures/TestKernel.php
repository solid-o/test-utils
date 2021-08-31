<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\fixtures;

use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

use function sys_get_temp_dir;

class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public static int $bootCount = 0;
    public static int $shutdownCount = 0;

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return sys_get_temp_dir();
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', ['test' => true]);

        $container->register('logger', NullLogger::class);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }

    public function boot(): void
    {
        if (! $this->booted) {
            self::$bootCount++;
        }

        parent::boot();
    }

    public function shutdown(): void
    {
        if ($this->booted) {
            self::$shutdownCount++;
        }

        parent::shutdown();
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }
}
