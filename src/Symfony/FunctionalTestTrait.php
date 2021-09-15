<?php

declare(strict_types=1);

namespace Solido\TestUtils\Symfony;

use Generator;
use PHPUnit\Framework\Constraint\LogicalNot;
use Solido\Common\Urn\Urn;
use Solido\PolicyChecker\DataCollector\PolicyCheckerDataCollector;
use Solido\PolicyChecker\Test\TestPolicyChecker;
use Solido\TestUtils\Constraint\ResponseHasHeaders;
use Solido\TestUtils\Constraint\SecurityPolicyChecked;
use Solido\TestUtils\ResponseStatusTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\VarDumper\Cloner\Data;

use function assert;
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

    private static ?KernelBrowser $client = null;
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
    abstract protected static function ensureKernelShutdown(); // phpcs:ignore

    /**
     * Adds a policy for the current test.
     *
     * @param string|string[]|Urn|null $subjects
     * @param string|string[]|Urn|null $actions
     * @param string|string[]|Urn|null $resources
     */
    protected static function addGrant(string $effect, $subjects, $actions, $resources): void
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
    private static function getMergePatchHeader(?string $version = null): array
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
        array $server = []
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
        $requestData = null,
        array $additionalHeaders = [],
        array $files = [],
        array $server = []
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
        $requestData = null,
        array $additionalHeaders = [],
        array $files = [],
        array $server = []
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
        $requestData = null,
        array $additionalHeaders = [],
        array $files = [],
        array $server = []
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
        array $server = []
    ): Response {
        return self::request($url, 'DELETE', null, $additionalHeaders, [], $server);
    }

    /**
     * Performs a request.
     *
     * @param array<string, mixed>|string $requestData
     * @param array<string, string> $additionalHeaders
     * @param UploadedFile[] $files
     * @param array<string, string> $server
     */
    private static function request(
        string $url,
        string $method,
        $requestData = null,
        array $additionalHeaders = [],
        array $files = [],
        array $server = []
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
        static::$client->enableProfiler();

        ob_start();
        static::$client->request(
            $request->getMethod(),
            $request->getUri(),
            $request->getParameters(),
            $request->getFiles(),
            $request->getServer(),
            $request->getContent()
        );

        $response = self::$client->getResponse();
        if ($response instanceof StreamedResponse) {
            $contents = ob_get_clean();
            $result = new Response($contents, $response->getStatusCode(), $response->headers->all());
            $result->setStatusCode(
                $response->getStatusCode(),
                (fn () => $this->statusText)->bindTo($response, Response::class)() // phpcs:ignore Squiz.Scope.StaticThisUsage.Found
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
     * @internal
     *
     * @param string[][] $headers
     *
     * @return iterable<string, string[]>
     */
    public static function _formatPhpHeaders(array $headers): Generator // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
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
     *
     * @param mixed ...$policies
     */
    public static function assertGrantHasBeenChecked(...$policies): void
    {
        $profile = self::$client->getProfile();
        $securityCollector = $profile->getCollector('security');
        assert($securityCollector instanceof PolicyCheckerDataCollector);

        $data = $securityCollector->getPolicyPermissions();
        assert($data instanceof Data);
        $checked = $data->getValue(true);

        self::assertThat($policies, new SecurityPolicyChecked(...$checked));
    }

    public static function assertResponseHasHeader(string $header, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new ResponseHasHeaders([$header]), $message);
    }

    public static function assertResponseHasNotHeader(string $header, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new LogicalNot(new ResponseHasHeaders([$header])), $message);
    }

    private static function getClient(): KernelBrowser
    {
        if (! static::$client instanceof KernelBrowser) {
            static::fail(sprintf('A client must be set to make assertions on it. Did you forget to call "%s::createClient"?', self::class));
        }

        return static::$client;
    }

    private static function getResponse(): Response
    {
        $response = static::getClient()->getResponse();
        if ($response === null) {
            static::fail('A client must have an HTTP Response to make assertions. Did you forget to make an HTTP request?');
        }

        return $response;
    }
}
