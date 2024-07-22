<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\fixtures\Laravel;

use Illuminate\Foundation\Http\Kernel;

class TestKernel extends Kernel
{
    public static int $bootCount = 0;
    public static int $shutdownCount = 0;

    public function bootstrap(): void
    {
        self::$bootCount++;

        parent::bootstrap();
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
