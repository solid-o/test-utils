<?php

declare(strict_types=1);

namespace Solido\TestUtils\Laravel;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use function assert;
use function class_exists;
use function func_num_args;
use function Safe\getcwd;
use function Safe\sprintf;

class WebTestCase extends TestCase
{
    protected static ?Application $kernel = null;
    protected static bool $booted = false;
    protected static string $kernelClass;

    protected function tearDown(): void
    {
        static::ensureKernelShutdown();
        static::$kernel = null;
        static::$booted = false;
    }

    /**
     * Boots the Kernel for this test.
     */
    protected static function bootKernel(): HttpKernelInterface
    {
        static::ensureKernelShutdown();

        static::$kernel = new Application(static::getApplicationBasePath());
        if (! isset(static::$kernelClass)) {
            static::$kernelClass = static::getKernelClass();
        }

        static::$kernel->singleton(HttpKernelContract::class, static::$kernelClass);
        static::$kernel->singleton(
            ExceptionHandler::class,
            $_SERVER['EXCEPTION_HANDLER_CLASS'] ?? $_ENV['EXCEPTION_HANDLER_CLASS'] ?? Handler::class
        );

        static::$booted = true;

        return static::$kernel;
    }

    protected static function getApplicationBasePath(): string
    {
        return $_ENV['APP_BASE_PATH'] ?? getcwd();
    }

    /**
     * Shuts the kernel down if it was used in the test - called by the tearDown method by default.
     */
    protected static function ensureKernelShutdown(): void
    {
        if (static::$kernel === null) {
            return;
        }

        static::$kernel->terminate();
        static::$booted = false;
    }

    /**
     * Creates a KernelBrowser.
     *
     * @param array<string, bool|string> $options An array of options to pass to the createKernel class
     * @param array<string, string>      $server  An array of server parameters
     *
     * @return KernelBrowser A Client instance
     */
    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        if (static::$booted) {
            throw new LogicException(sprintf('Booting the kernel before calling "%s()" is not supported, the kernel should only be booted once.', __METHOD__));
        }

        $kernel = static::bootKernel();

        $client = new KernelBrowser($kernel, $server);
        $client->setServerParameters($server);

        return self::getClient($client); /* @phpstan-ignore-line */
    }

    /**
     * @return string The Kernel class name
     */
    protected static function getKernelClass(): string
    {
        if (! isset($_SERVER['KERNEL_CLASS']) && ! isset($_ENV['KERNEL_CLASS'])) {
            throw new LogicException(sprintf('You must set the KERNEL_CLASS environment variable to the fully-qualified class name of your Kernel in phpunit.xml / phpunit.xml.dist or override the "%1$s::createKernel()" or "%1$s::getKernelClass()" method.', static::class));
        }

        $class = $_ENV['KERNEL_CLASS'] ?? $_SERVER['KERNEL_CLASS'];
        if (! class_exists($class)) {
            throw new RuntimeException(sprintf('Class "%s" doesn\'t exist or cannot be autoloaded. Check that the KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel or override the "%s::createKernel()" method.', $class, static::class));
        }

        return $class;
    }

    private static function getClient(?AbstractBrowser $newClient = null): AbstractBrowser
    {
        static $client;

        if (0 < func_num_args()) {
            assert($newClient !== null);

            return $client = $newClient;
        }

        if (! $client instanceof AbstractBrowser) {
            self::fail(sprintf('A client must be set to make assertions on it. Did you forget to call "%s::createClient()"?', self::class));
        }

        return $client;
    }

    private static function getResponse(): Response
    {
        $response = self::getClient()->getResponse();
        if ($response === null) { /* @phpstan-ignore-line */
            self::fail('A client must have an HTTP Response to make assertions. Did you forget to make an HTTP request?');
        }

        assert($response instanceof Response);

        return $response;
    }

    private static function getRequest(): Request
    {
        $request = self::getClient()->getRequest();
        if ($request === null) { /* @phpstan-ignore-line */
            self::fail('A client must have an HTTP Request to make assertions. Did you forget to make an HTTP request?');
        }

        assert($request instanceof Request);

        return $request;
    }
}
