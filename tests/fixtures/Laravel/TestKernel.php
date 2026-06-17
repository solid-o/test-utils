<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\fixtures\Laravel;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Http\Response;

class TestKernel extends Kernel
{
    public static int $bootCount = 0;
    public static int $shutdownCount = 0;

    public function bootstrap(): void
    {
        self::$bootCount++;

        parent::bootstrap();

        $this->app['router']->get('/', static fn (): Response => new Response('', Response::HTTP_OK));
    }

    public function terminate($request, $response): void
    {
        parent::terminate($request, $response);

        self::$shutdownCount++;
    }

    public static function getBasePath(): string
    {
        return __DIR__;
    }
}
