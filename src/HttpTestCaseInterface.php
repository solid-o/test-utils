<?php

declare(strict_types=1);

namespace Solido\TestUtils;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

interface HttpTestCaseInterface
{
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
    ): Response;
}
