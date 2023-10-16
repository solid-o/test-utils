<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Solido\Common\ResponseAdapter\ResponseAdapterInterface;

use function json_decode;
use function json_last_error;
use function Safe\preg_match;

use const JSON_ERROR_NONE;

/** @internal */
trait ResponseJsonContentTrait
{
    private function isJson(ResponseAdapterInterface $response): bool
    {
        if (! preg_match('/application\/json/', $response->getContentType())) {
            return false;
        }

        $content = $response->getContent();

        /** @phpstan-ignore-next-line */
        @json_decode($content);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
