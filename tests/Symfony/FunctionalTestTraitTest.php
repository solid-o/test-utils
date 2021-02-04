<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Symfony;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Solido\PolicyChecker\DataCollector\PolicyCheckerDataCollector;
use Solido\TestUtils\Symfony\FunctionalTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\VarDumper\Cloner\Data;

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

        $client->request(
            'GET',
            '/',
            [],
            Argument::any(),
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
            [new UploadedFile(__DIR__ . '/../fixtures/photo.jpg', 'photo.jpg', 'image/jpeg', UPLOAD_ERR_OK, true)]
        );
    }
}

class ConcreteFunctionalTestTrait extends Assert
{
    use FunctionalTestTrait {
        request as public;
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
