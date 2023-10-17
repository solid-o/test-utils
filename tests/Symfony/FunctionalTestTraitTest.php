<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Symfony;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Solido\PolicyChecker\DataCollector\PolicyCheckerDataCollector;
use Solido\TestUtils\HttpTestCaseInterface;
use Solido\TestUtils\Symfony\FunctionalTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\VarDumper\Cloner\Data;

use function ob_get_clean;
use function ob_start;

use const UPLOAD_ERR_OK;

class FunctionalTestTraitTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        (static fn () => ConcreteFunctionalTestTrait::$client = null)
            ->bindTo(null, ConcreteFunctionalTestTrait::class)();
    }

    public function testAssertGrantHasBeenChecked(): void
    {
        $client = $this->prophesize(KernelBrowser::class);
        $client->getProfile()
            ->willReturn($profile = $this->prophesize(Profile::class));

        $profile->getCollector('security')
            ->willReturn($collector = $this->prophesize(PolicyCheckerDataCollector::class));

        $collector->getPolicyPermissions()->willReturn($data = $this->prophesize(Data::class));
        $data->getValue(true)->willReturn([['action' => 'Get']]);

        ConcreteFunctionalTestTrait::setClient($client->reveal());
        ConcreteFunctionalTestTrait::assertGrantHasBeenChecked('Get');

        $this->expectException(ExpectationFailedException::class);
        ConcreteFunctionalTestTrait::assertGrantHasBeenChecked('Edit');
    }

    public function testRequestWithoutFiles(): void
    {
        $client = $this->prophesize(KernelBrowser::class);
        ConcreteFunctionalTestTrait::setClient($client->reveal());

        $client->request(
            'GET',
            '/',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            null
        )->shouldBeCalled();

        $client->enableProfiler()->shouldBeCalled();
        $client->getResponse()->willReturn(new Response());

        ConcreteFunctionalTestTrait::request('/', 'GET', null, ['Accept' => 'application/json']);
    }

    public function testRequestContractShouldPass(): void
    {
        $client = $this->prophesize(KernelBrowser::class);
        ConcreteFunctionalTestTrait::setClient($client->reveal());

        $client->request(
            'GET',
            '/',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            null
        )->shouldBeCalled();

        $client->enableProfiler()->shouldBeCalled();
        $client->getResponse()->willReturn(new Response());

        $testCase = new ConcreteFunctionalTestTrait();
        $testCase->buildRequest()
            ->withPath('/')
            ->withMethod('GET')
            ->withHeader('Accept', 'application/json')
            ->expectResponse()
            ->shouldHaveSuccessStatus();

        $testCase->checkContracts();
    }

    public function testRequestContractShouldFail(): void
    {
        $client = $this->prophesize(KernelBrowser::class);
        ConcreteFunctionalTestTrait::setClient($client->reveal());

        $client->request(
            'GET',
            '/',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            null
        )->shouldBeCalled();

        $client->enableProfiler()->shouldBeCalled();
        $client->getResponse()->willReturn(new Response());

        $testCase = new ConcreteFunctionalTestTrait();
        $testCase->buildRequest()
            ->withPath('/')
            ->withMethod('GET')
            ->withHeader('Accept', 'application/json')
            ->expectResponse()
            ->shouldBeJson();

        try {
            $testCase->checkContracts();
            self::fail('Expected fail');
        } catch (AssertionFailedError $e) {
            self::assertEquals('Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.', $e->toString());
        }
    }

    public function testOnPreRequestIsCalled(): void
    {
        $client = $this->prophesize(KernelBrowser::class);
        PreRequestConcreteFunctionalTestTrait::setClient($client->reveal());

        $client->request('POST', '/modified_url', Argument::cetera())->shouldBeCalled();

        $client->enableProfiler()->shouldBeCalled();
        $client->getResponse()->willReturn(new Response());

        PreRequestConcreteFunctionalTestTrait::request('/', 'GET', null, ['Accept' => 'application/json']);
    }

    public function testRequestWithFiles(): void
    {
        $client = $this->prophesize(KernelBrowser::class);
        ConcreteFunctionalTestTrait::setClient($client->reveal());

        $file = new UploadedFile(__DIR__ . '/../fixtures/photo.jpg', 'photo.jpg', 'image/jpeg', UPLOAD_ERR_OK, true);
        $client->request(
            'GET',
            '/',
            [],
            Argument::that(function (array $v) use ($file) {
                Assert::assertSame($file, $v[0]);
                return true;
            }),
            ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'multipart/form-data'],
            null
        )->shouldBeCalled();

        $client->enableProfiler()->shouldBeCalled();
        $client->getResponse()->willReturn(new Response());

        ConcreteFunctionalTestTrait::request(
            '/',
            'GET',
            null,
            ['Accept' => 'application/json'],
            [$file]
        );
    }

    public function testShouldIgnoredStreamedOutput(): void
    {
        $crawler = $this->prophesize(Crawler::class);
        $client = $this->prophesize(KernelBrowser::class);
        ConcreteFunctionalTestTrait::setClient($client->reveal());

        $response = new StreamedResponse(static function (): void {
            echo 'this should not be visible';
        }, Response::HTTP_CREATED);

        $client->request('GET', '/', Argument::cetera())
            ->will(function () use ($crawler, $response): Crawler { // phpcs:ignore
                $response->prepare(new Request());
                $response->send();

                return $crawler->reveal();
            })
            ->shouldBeCalled();

        $client->enableProfiler()->shouldBeCalled();
        $client->getResponse()->willReturn($response);

        ob_start();
        $response = ConcreteFunctionalTestTrait::request('/', 'GET');
        self::assertEquals('1.1', $response->getProtocolVersion());
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertEquals('', ob_get_clean());
        self::assertEquals('this should not be visible', $response->getContent());
    }
}

class ConcreteFunctionalTestTrait extends Assert implements HttpTestCaseInterface
{
    use FunctionalTestTrait {
        buildRequest as public;
    }

    public static Response $response;

    public static function setClient(KernelBrowser $client): void
    {
        static::$client = $client;
    }

    public static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        return static::$client;
    }

    protected static function ensureKernelShutdown(): void
    {
    }

    public static function getResponse(): Response
    {
        return self::$response;
    }
}

class PreRequestConcreteFunctionalTestTrait extends ConcreteFunctionalTestTrait
{
    protected static function onPreRequest(BrowserKitRequest $request): BrowserKitRequest
    {
        return new BrowserKitRequest(
            '/modified_url',
            'POST',
            $request->getParameters(),
            $request->getFiles(),
            $request->getCookies(),
            $request->getServer(),
            $request->getContent()
        );
    }
}
