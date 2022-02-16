<?php

declare(strict_types=1);

namespace Solido\TestUtils\Laravel;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutEvents;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

use function assert;
use function class_exists;
use function class_uses_recursive;
use function debug_backtrace;
use function func_num_args;
use function get_class;
use function Safe\array_flip;
use function Safe\getcwd;
use function Safe\sprintf;
use function trigger_error;

use const DEBUG_BACKTRACE_PROVIDE_OBJECT;
use const E_USER_NOTICE;

class WebTestCase extends TestCase
{
    protected static ?Application $kernel = null;
    protected static bool $booted = false;
    protected static string $kernelClass;
    protected static string $consoleKernelClass;

    /**
     * The callbacks that should be run after the application is created.
     *
     * @var callable[]
     */
    protected static array $afterApplicationCreatedCallbacks = [];

    /**
     * The callbacks that should be run before the application is destroyed.
     *
     * @var callable[]
     */
    protected static array $beforeApplicationDestroyedCallbacks = [];

    /**
     * The exception thrown while running an application destruction callback.
     */
    protected static ?Throwable $callbackException = null;

    /**
     * Register a callback to be run after the application is created.
     */
    public static function afterApplicationCreated(callable $callback): void
    {
        static::$afterApplicationCreatedCallbacks[] = $callback;

        if (! static::$booted) {
            return;
        }

        $callback();
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name === 'app') {
            static::bootKernel();
            $returnValue = &static::$kernel;
        } else {
            $reflector = new ReflectionClass(static::class);
            if (! $reflector->hasProperty($name)) {
                $backtrace = debug_backtrace(0, 1);
                trigger_error(
                    sprintf(
                        'Undefined property: %s::$%s in %s on line %s',
                        $reflector->getName(),
                        $name,
                        $backtrace[0]['file'] ?? '<unknown>',
                        $backtrace[0]['line'] ?? '<unknown>'
                    ),
                    E_USER_NOTICE
                );

                return $this->$name;
            }

            $targetObject = $this;
            $accessor = static function & () use ($targetObject, $name) {
                return $targetObject->$name;
            };
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            $scopeObject = $backtrace[1]['object'] ?? new stdClass();
            $scopeClass = get_class($scopeObject);

            assert($scopeClass !== false);
            $accessor = $accessor->bindTo($scopeObject, $scopeClass);

            $returnValue = &$accessor();
        }

        return $returnValue;
    }

    protected function tearDown(): void
    {
        static::ensureKernelShutdown();
        static::$kernel = null;
        static::$booted = false;

        static::$afterApplicationCreatedCallbacks = [];
        static::$beforeApplicationDestroyedCallbacks = [];
        static::$callbackException = null;
    }

    /**
     * Boot the testing helper traits.
     */
    protected function setUpTraits(): void
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            $this->refreshDatabase(); /* @phpstan-ignore-line */
        }

        if (isset($uses[DatabaseMigrations::class])) {
            $this->runDatabaseMigrations(); /* @phpstan-ignore-line */
        }

        if (isset($uses[DatabaseTransactions::class])) {
            $this->beginDatabaseTransaction(); /* @phpstan-ignore-line */
        }

        if (isset($uses[WithoutMiddleware::class])) {
            $this->disableMiddlewareForAllTests(); /* @phpstan-ignore-line */
        }

        if (isset($uses[WithoutEvents::class])) {
            $this->disableEventsForAllTests(); /* @phpstan-ignore-line */
        }

        if (! isset($uses[WithFaker::class])) {
            return;
        }

        $this->setUpFaker(); /* @phpstan-ignore-line */
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

        if (! isset(static::$consoleKernelClass)) {
            static::$consoleKernelClass = static::getConsoleKernelClass();
        }

        static::$kernel->singleton(HttpKernelContract::class, static::$kernelClass);
        static::$kernel->singleton(ConsoleKernelContract::class, static::$consoleKernelClass);

        static::$kernel->singleton(
            ExceptionHandler::class,
            $_SERVER['EXCEPTION_HANDLER_CLASS'] ?? $_ENV['EXCEPTION_HANDLER_CLASS'] ?? Handler::class
        );

        foreach (static::$afterApplicationCreatedCallbacks as $callback) {
            $callback();
        }

        $dispatcher = static::$kernel->get('events');
        assert($dispatcher instanceof Dispatcher);
        Model::setEventDispatcher($dispatcher);

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

        static::callBeforeApplicationDestroyedCallbacks();

        static::$kernel->flush();
        static::$kernel->terminate();
        static::$booted = false;

        if (self::$callbackException !== null) {
            throw self::$callbackException;
        }
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
            throw new LogicException(sprintf('You must set the KERNEL_CLASS environment variable to the fully-qualified class name of your Kernel in phpunit.xml / phpunit.xml.dist or override the "%1$s::getKernelClass()" method.', static::class));
        }

        $class = $_ENV['KERNEL_CLASS'] ?? $_SERVER['KERNEL_CLASS'];
        if (! class_exists($class)) {
            throw new RuntimeException(sprintf('Class "%s" doesn\'t exist or cannot be autoloaded. Check that the KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel.', $class));
        }

        return $class;
    }

    /**
     * @return string The Kernel class name
     */
    protected static function getConsoleKernelClass(): string
    {
        $class = $_ENV['CONSOLE_KERNEL_CLASS'] ?? $_SERVER['CONSOLE_KERNEL_CLASS'] ?? ConsoleKernel::class;
        if (! class_exists($class)) {
            throw new RuntimeException(sprintf('Class "%s" doesn\'t exist or cannot be autoloaded. Check that the CONSOLE_KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel.', $class));
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

    /**
     * Register a callback to be run before the application is destroyed.
     */
    protected static function beforeApplicationDestroyed(callable $callback): void
    {
        static::$beforeApplicationDestroyedCallbacks[] = $callback;
    }

    /**
     * Execute the application's pre-destruction callbacks.
     */
    protected static function callBeforeApplicationDestroyedCallbacks(): void
    {
        foreach (static::$beforeApplicationDestroyedCallbacks as $callback) {
            try {
                $callback();
            } catch (Throwable $e) { /* @phpstan-ignore-line */
                if (! static::$callbackException) {
                    static::$callbackException = $e;
                }
            }
        }
    }

    protected static function getResponse(): Response
    {
        $response = self::getClient()->getResponse();
        if ($response === null) { /* @phpstan-ignore-line */
            self::fail('A client must have an HTTP Response to make assertions. Did you forget to make an HTTP request?');
        }

        assert($response instanceof Response);

        return $response;
    }

    protected static function getRequest(): Request
    {
        $request = self::getClient()->getRequest();
        if ($request === null) { /* @phpstan-ignore-line */
            self::fail('A client must have an HTTP Request to make assertions. Did you forget to make an HTTP request?');
        }

        assert($request instanceof Request);

        return $request;
    }
}
