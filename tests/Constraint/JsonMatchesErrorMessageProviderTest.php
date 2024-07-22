<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use Solido\TestUtils\Constraint\JsonMatchesErrorMessageProvider;
use PHPUnit\Framework\TestCase;

class JsonMatchesErrorMessageProviderTest extends TestCase
{
    #[DataProvider('provideMessages')]
    public function testMessage(int $code, string $message): void
    {
        self::assertEquals($message, JsonMatchesErrorMessageProvider::determineJsonError($code));
    }

    public static function provideMessages(): iterable
    {
        yield [JSON_ERROR_NONE, ''];
        yield [JSON_ERROR_DEPTH, 'Maximum stack depth exceeded'];
        yield [JSON_ERROR_STATE_MISMATCH, 'Underflow or the modes mismatch'];
        yield [JSON_ERROR_CTRL_CHAR, 'Unexpected control character found'];
        yield [JSON_ERROR_SYNTAX, 'Syntax error, malformed JSON'];
        yield [JSON_ERROR_UTF8, 'Malformed UTF-8 characters, possibly incorrectly encoded'];
        yield [-1, 'Unknown error'];
    }
}
