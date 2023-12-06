<?php

declare(strict_types=1);

namespace Solido\TestUtils\Symfony;

use Generator;
use LogicException;
use PHPUnit\Framework\Constraint\LogicalNot;
use Solido\Common\Urn\Urn;
use Solido\PolicyChecker\DataCollector\PolicyCheckerDataCollector;
use Solido\PolicyChecker\Test\TestPolicyChecker;
use Solido\TestUtils\Constraint\ResponseHasHeaders;
use Solido\TestUtils\Constraint\ResponseHeaderSame;
use Solido\TestUtils\Constraint\ResponseSubset;
use Solido\TestUtils\Constraint\SecurityPolicyChecked;
use Solido\TestUtils\HttpTestCaseInterface;
use Solido\TestUtils\ResponseStatusTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Contracts\Service\ResetInterface;

use function assert;
use function gc_collect_cycles;
use function gc_mem_caches;
use function implode;
use function in_array;
use function is_array;
use function iterator_to_array;
use function json_encode;
use function ob_end_flush;
use function ob_get_clean;
use function ob_start;
use function sprintf;
use function str_replace;
use function strtolower;
use function strtoupper;

use const JSON_THROW_ON_ERROR;

trait FunctionalTestTrait
{
    use JsonResponseTrait;
    use ResponseStatusTrait;

    private static AbstractBrowser|null $client = null;
    private static string $authorizationToken;

    /**
     * Creates a Client.
     *
     * @param array<string, mixed> $options An array of options to pass to the createKernel class
     * @param array<string, mixed> $server  An array of server parameters
     *
     * @return KernelBrowser A Client instance
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    abstract protected static function createClient(array $options = [], array $server = []);

    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected static function ensureKernelShutdown(): void
    {
        if (static::$kernel === null) {
            return;
        }

        $container = static::$booted ? static::$kernel->getContainer() : null;
        static::$kernel->shutdown();
        static::$booted = false;

        if (! ($container instanceof ResetInterface)) {
            return;
        }

        $container->reset();
    }

    /**
     * Adds a policy for the current test.
     *
     * @param string|string[]|Urn|null $subjects
     * @param string|string[]|Urn|null $actions
     * @param string|string[]|Urn|null $resources
     */
    protected static function addGrant(string $effect, string|array|Urn|null $subjects, string|array|Urn|null $actions, string|array|Urn|null $resources): void
    {
        TestPolicyChecker::addGrant($effect, $subjects, $actions, $resources);
    }

    /**
     * Gets the accept header with the specified version.
     *
     * @return array<string, string>
     */
    private static function getAcceptHeader(string $version, string $format = 'json'): array
    {
        $mime = Request::getMimeTypes($format)[0] ?? 'text/plain';

        return ['Accept' => sprintf('%s; version=%s', $mime, $version)];
    }

    /**
     * Gets the merge patch header.
     * If version is specified, also the accept header with that version is returned.
     *
     * @return array<string, string>
     */
    private static function getMergePatchHeader(string|null $version = null): array
    {
        $mergePatchHeader = ['Content-Type' => 'application/merge-patch+json'];
        if ($version === null) {
            return $mergePatchHeader;
        }

        return $mergePatchHeader + self::getAcceptHeader($version);
    }

    /**
     * Executes a GET request.
     *
     * @param array<string, string> $additionalHeaders
     * @param array<string, string> $server
     */
    private static function get(
        string $url,
        array $additionalHeaders = [],
        array $server = [],
    ): Response {
        return self::request($url, 'GET', null, $additionalHeaders, [], $server);
    }

    /**
     * Executes a POST request.
     *
     * @param array<string, mixed>|string $requestData
     * @param array<string, string> $additionalHeaders
     * @param UploadedFile[] $files
     * @param array<string, string> $server
     */
    private static function post(
        string $url,
        array|string|null $requestData = null,
        array $additionalHeaders = [],
        array $files = [],
        array $server = [],
    ): Response {
        return self::request($url, 'POST', $requestData, $additionalHeaders, $files, $server);
    }

    /**
     * Executes a PUT request.
     *
     * @param array<string, mixed>|string $requestData
     * @param array<string, string> $additionalHeaders
     * @param UploadedFile[] $files
     * @param array<string, string> $server
     */
    private static function put(
        string $url,
        array|string|null $requestData = null,
        array $additionalHeaders = [],
        array $files = [],
        array $server = [],
    ): Response {
        return self::request($url, 'PUT', $requestData, $additionalHeaders, $files, $server);
    }

    /**
     * Executes a PATCH request.
     *
     * @param array<string, mixed>|string $requestData
     * @param array<string, string> $additionalHeaders
     * @param UploadedFile[] $files
     * @param array<string, string> $server
     */
    private static function patch(
        string $url,
        array|string|null $requestData = null,
        array $additionalHeaders = [],
        array $files = [],
        array $server = [],
    ): Response {
        return self::request($url, 'PATCH', $requestData, $additionalHeaders, $files, $server);
    }

    /**
     * Executes a DELETE request.
     *
     * @param array<string, string>  $additionalHeaders
     * @param array<string, string> $server
     */
    private static function delete(
        string $url,
        array $additionalHeaders = [],
        array $server = [],
    ): Response {
        return self::request($url, 'DELETE', null, $additionalHeaders, [], $server);
    }

    public function buildRequest(): \Solido\TestUtils\Symfony\Request
    {
        if (! $this instanceof HttpTestCaseInterface) {
            throw new LogicException(sprintf('You need to implement %s in order to use contract testing methods.', HttpTestCaseInterface::class));
        }

        $this->checkContracts();

        return new \Solido\TestUtils\Symfony\Request($this);
    }

    /** @postCondition */
    public function checkContracts(): void
    {
        gc_collect_cycles();
        gc_mem_caches();
    }

    /**
     * Performs a request.
     *
     * @param array<string, mixed>|string $requestData
     * @param array<string, string> $additionalHeaders
     * @param UploadedFile[] $files
     * @param array<string, string> $server
     */
    public static function request(
        string $url,
        string $method,
        array|string|null $requestData = null,
        array $additionalHeaders = [],
        array $files = [],
        array $server = [],
    ): Response {
        $headers = new HeaderBag(['accept' => 'application/json']);
        $headers->add($additionalHeaders);

        if (! empty($files)) {
            $headers->set('content-type', 'multipart/form-data');
        } elseif ($requestData === null || is_array($requestData)) {
            $requestData = $requestData !== null ? json_encode($requestData, JSON_THROW_ON_ERROR) : null;
            if (! $headers->has('content-type')) {
                $headers->set('content-type', 'application/json');
            }
        }

        $request = new BrowserKitRequest($url, $method, [], $files, [], iterator_to_array(self::_formatPhpHeaders($headers->all())) + $server, $requestData);
        $request = static::onPreRequest($request);

        static::ensureKernelShutdown();
        static::$client = static::createClient();
        static::enableProfiler();

        ob_start();
        static::$client->request(
            $request->getMethod(),
            $request->getUri(),
            $request->getParameters(),
            $request->getFiles(),
            $request->getServer(),
            $request->getContent(),
        );

        $response = self::$client->getResponse();
        if ($response instanceof StreamedResponse) {
            $contents = ob_get_clean();
            $result = new Response($contents, $response->getStatusCode(), $response->headers->all());
            $result->setStatusCode(
                $response->getStatusCode(),
                (fn () => $this->statusText)->bindTo($response, Response::class)(), // phpcs:ignore Squiz.Scope.StaticThisUsage.Found
            );

            $charset = $response->getCharset();
            if ($charset !== null) {
                $result->setCharset($charset);
            }

            $result->setProtocolVersion($response->getProtocolVersion());

            $response = $result;
        } else {
            ob_end_flush();
        }

        return $response;
    }

    /**
     * Override this to customize request (ex: add custom authorization).
     */
    protected static function onPreRequest(BrowserKitRequest $request): BrowserKitRequest
    {
        return $request;
    }

    /**
     * @param string[][] $headers
     *
     * @return Generator<string, string[]>
     */
    private static function _formatPhpHeaders(array $headers): Generator // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $keyToMaintain = ['content-length', 'content-md5', 'content-type', 'php_auth_user', 'php_auth_pw'];

        foreach ($headers as $key => $value) {
            $key = (in_array(strtolower($key), $keyToMaintain) ? '' : 'HTTP_')
                . str_replace('-', '_', strtoupper($key));

            yield $key => is_array($value) ? implode(',', $value) : $value;
        }
    }

    /**
     * Checks that the specified policies have been checked by security policy checker component.
     */
    public static function assertGrantHasBeenChecked(mixed ...$policies): void
    {
        $checked = (static function (): array|null {
            $client = self::$client;
            if (! $client instanceof KernelBrowser) {
                return null;
            }

            $profile = $client->getProfile();
            $securityCollector = $profile->getCollector('security');
            if (! $securityCollector instanceof PolicyCheckerDataCollector) {
                return null;
            }

            $data = $securityCollector->getPolicyPermissions();
            assert($data instanceof Data);

            return $data->getValue(true);
        })();

        if ($checked === null) {
            self::markTestIncomplete('Cannot assert on checked grant policies: profile informations not available or security policy checker component not installed/enabled.');
        }

        self::assertThat($policies, new SecurityPolicyChecked(...$checked));
    }

    /** @param string|array<string|int, mixed>|object $subset */
    public static function assertResponseContainsSubset(string|array|object $subset, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new ResponseSubset($subset), $message);
    }

    public static function assertResponseHeaderSame(string $headerName, string $headerValue, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new ResponseHeaderSame($headerName, $headerValue), $message);
    }

    public static function assertResponseHasHeader(string $header, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new ResponseHasHeaders([$header]), $message);
    }

    public static function assertResponseHasNotHeader(string $header, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new LogicalNot(new ResponseHasHeaders([$header])), $message);
    }

    private static function enableProfiler(): void
    {
        static::$client->enableProfiler();
    }

    protected static function getClient(AbstractBrowser $newClient = null): KernelBrowser
    {
        if (0 < \func_num_args()) {
            return static::$client = $newClient;
        }

        if (! static::$client instanceof KernelBrowser) {
            static::fail(sprintf('A client must be set to make assertions on it. Did you forget to call "%s::createClient"?', self::class));
        }

        return static::$client;
    }

    protected static function getResponse(): Response
    {
        $response = static::getClient()->getResponse();
        if ($response === null) {
            static::fail('A client must have an HTTP Response to make assertions. Did you forget to make an HTTP request?');
        }

        return $response;
    }
}
