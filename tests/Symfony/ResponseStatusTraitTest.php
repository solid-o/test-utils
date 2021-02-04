<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Symfony;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Symfony\ResponseStatusTrait;
use Symfony\Component\HttpFoundation\Response;

class ResponseStatusTraitTest extends TestCase
{
    public function testAssertResponseIs(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 200);
        ConcreteResponseStatusTrait::assertResponseIs(200);

        ConcreteResponseStatusTrait::$response = new Response('', 500);
        ConcreteResponseStatusTrait::assertResponseIs(500);
    }

    public function testAssertResponseIsOk(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 200);
        ConcreteResponseStatusTrait::assertResponseIsOk();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response('', 500);
        ConcreteResponseStatusTrait::assertResponseIsOk();
    }

    public function testAssertResponseIsCreated(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 201);
        ConcreteResponseStatusTrait::assertResponseIsCreated();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsCreated();
    }

    public function testAssertResponseIsAccepted(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 202);
        ConcreteResponseStatusTrait::assertResponseIsAccepted();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsAccepted();
    }

    public function testAssertResponseIsNoContent(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 204);
        ConcreteResponseStatusTrait::assertResponseIsNoContent();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsNoContent();
    }

    public function testAssertResponseIsBadRequest(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 400);
        ConcreteResponseStatusTrait::assertResponseIsBadRequest();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsBadRequest();
    }

    public function testAssertResponseIsUnauthorized(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 401);
        ConcreteResponseStatusTrait::assertResponseIsUnauthorized();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsUnauthorized();
    }

    public function testAssertResponseIsPaymentRequirement(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 402);
        ConcreteResponseStatusTrait::assertResponseIsPaymentRequired();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsPaymentRequired();
    }

    public function testAssertResponseIsMethodNotAllowed(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 405);
        ConcreteResponseStatusTrait::assertResponseIsMethodNotAllowed();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsMethodNotAllowed();
    }

    public function testAssertResponseIsPreconditionFailed(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 412);
        ConcreteResponseStatusTrait::assertResponseIsPreconditionFailed();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsPreconditionFailed();
    }

    public function testAssertResponseIsUnprocessableEntity(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 422);
        ConcreteResponseStatusTrait::assertResponseIsUnprocessableEntity();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsUnprocessableEntity();
    }

    public function testAssertResponseIsForbidden(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 403);
        ConcreteResponseStatusTrait::assertResponseIsForbidden();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsForbidden();
    }

    public function testAssertResponseIsNotFound(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 404);
        ConcreteResponseStatusTrait::assertResponseIsNotFound();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsNotFound();
    }

    public function testAssertResponseIsRedirect(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 301);
        ConcreteResponseStatusTrait::assertResponseIsRedirect();
        ConcreteResponseStatusTrait::$response = new Response('', 302);
        ConcreteResponseStatusTrait::assertResponseIsRedirect();
        ConcreteResponseStatusTrait::$response = new Response('', 303);
        ConcreteResponseStatusTrait::assertResponseIsRedirect();
        ConcreteResponseStatusTrait::$response = new Response('', 307);
        ConcreteResponseStatusTrait::assertResponseIsRedirect();

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsNotRedirect();
        ConcreteResponseStatusTrait::$response = new Response('', 400);
        ConcreteResponseStatusTrait::assertResponseIsNotRedirect();
        ConcreteResponseStatusTrait::$response = new Response('', 500);
        ConcreteResponseStatusTrait::assertResponseIsNotRedirect();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response();
        ConcreteResponseStatusTrait::assertResponseIsRedirect();
    }

    public function testAssertResponseIsSuccessful(): void
    {
        ConcreteResponseStatusTrait::$response = new Response('', 200);
        ConcreteResponseStatusTrait::assertResponseIsSuccessful();
        ConcreteResponseStatusTrait::$response = new Response('', 201);
        ConcreteResponseStatusTrait::assertResponseIsSuccessful();
        ConcreteResponseStatusTrait::$response = new Response('', 202);
        ConcreteResponseStatusTrait::assertResponseIsSuccessful();
        ConcreteResponseStatusTrait::$response = new Response('', 204);
        ConcreteResponseStatusTrait::assertResponseIsSuccessful();

        ConcreteResponseStatusTrait::$response = new Response('', 302);
        ConcreteResponseStatusTrait::assertResponseIsNotSuccessful();
        ConcreteResponseStatusTrait::$response = new Response('', 400);
        ConcreteResponseStatusTrait::assertResponseIsNotSuccessful();
        ConcreteResponseStatusTrait::$response = new Response('', 500);
        ConcreteResponseStatusTrait::assertResponseIsNotSuccessful();

        $this->expectException(ExpectationFailedException::class);

        ConcreteResponseStatusTrait::$response = new Response('', 500);
        ConcreteResponseStatusTrait::assertResponseIsSuccessful();
    }
}

class ConcreteResponseStatusTrait extends Assert
{
    use ResponseStatusTrait;

    public static Response $response;

    public static function getResponse(): Response
    {
        return self::$response;
    }
}
