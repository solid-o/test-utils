<?php

declare(strict_types=1);

namespace Solido\TestUtils;

use PHPUnit\Framework\Constraint\LogicalNot;
use Solido\TestUtils\Constraint\ResponseIsRedirection;
use Solido\TestUtils\Constraint\ResponseIsSuccessful;
use Solido\TestUtils\Constraint\ResponseStatusCode;
use Symfony\Component\HttpFoundation\Response;

trait ResponseStatusTrait
{
    public static function assertResponseIs(int $expectedCode, string $message = ''): void
    {
        $response = static::getResponse();
        self::assertThat($response, new ResponseStatusCode($expectedCode), $message);
    }

    public static function assertResponseIsOk(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_OK, $message);
    }

    public static function assertResponseIsCreated(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_CREATED, $message);
    }

    public static function assertResponseIsAccepted(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_ACCEPTED, $message);
    }

    public static function assertResponseIsNoContent(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_NO_CONTENT, $message);
    }

    public static function assertResponseIsBadRequest(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_BAD_REQUEST, $message);
    }

    public static function assertResponseIsUnauthorized(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_UNAUTHORIZED, $message);
    }

    public static function assertResponseIsPaymentRequired(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_PAYMENT_REQUIRED, $message);
    }

    public static function assertResponseIsForbidden(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_FORBIDDEN, $message);
    }

    public static function assertResponseIsNotFound(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_NOT_FOUND, $message);
    }

    public static function assertResponseIsMethodNotAllowed(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_METHOD_NOT_ALLOWED, $message);
    }

    public static function assertResponseIsPreconditionFailed(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_PRECONDITION_FAILED, $message);
    }

    public static function assertResponseIsUnprocessableEntity(string $message = ''): void
    {
        self::assertResponseIs(Response::HTTP_UNPROCESSABLE_ENTITY, $message);
    }

    public static function assertResponseIsRedirect(string $message = ''): void
    {
        self::assertThat(static::getResponse(), new ResponseIsRedirection(), $message);
    }

    public static function assertResponseIsNotRedirect(string $message = ''): void
    {
        self::assertThat(static::getResponse(), new LogicalNot(new ResponseIsRedirection()), $message);
    }

    public static function assertResponseIsSuccessful(string $message = ''): void
    {
        self::assertThat(static::getResponse(), new ResponseIsSuccessful(), $message);
    }

    public static function assertResponseIsNotSuccessful(string $message = ''): void
    {
        self::assertThat(static::getResponse(), new LogicalNot(new ResponseIsSuccessful()), $message);
    }
}
