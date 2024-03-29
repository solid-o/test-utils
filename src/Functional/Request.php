<?php

declare(strict_types=1);

namespace Solido\TestUtils\Functional;

use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

use function sprintf;

abstract class Request
{
    protected string $method;
    protected string $path;

    /** @var array<string, string[]> */
    protected array $headers;
    /** @var array<string, mixed>|string|null */
    protected array|string|null $content = null;
    /** @var array<string, UploadedFileInterface> */
    protected array $files;

    public function __construct()
    {
        $this->method = 'GET';
        $this->path = '/';
        $this->headers = [];
        $this->content = null;
        $this->files = [];
    }

    public function withPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function withMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /** @param string|string[] $value */
    public function withHeader(string $name, string|array $value): self
    {
        $this->headers[$name] = (array) $value;

        return $this;
    }

    public function withAcceptHeader(string $format = 'json', string $version = ''): self
    {
        $mime = HttpFoundationRequest::getMimeTypes($format)[0] ?? 'text/plain';

        return $this->withHeader('Accept', empty($version) ? $mime : sprintf('%s; version=%s', $mime, $version));
    }

    /** @param array<string, mixed>|string|null $content */
    public function withContent(array|string|null $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function withFile(string $name, UploadedFileInterface $file): self
    {
        $this->files[$name] = $file;

        return $this;
    }

    /**
     * Create a Response object to apply constraints onto.
     */
    abstract public function expectResponse(): Response;
}
