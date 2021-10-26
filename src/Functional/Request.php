<?php

declare(strict_types=1);

namespace Solido\TestUtils\Functional;

use Psr\Http\Message\UploadedFileInterface;

abstract class Request
{
    protected string $method;
    protected string $path;

    /** @var array<string, string[]> */
    protected array $headers;
    /** @var mixed */
    protected $content;
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

    /**
     * @param string|string[] $value
     */
    public function withHeader(string $name, $value): self
    {
        $this->headers[$name] = (array) $value;

        return $this;
    }

    /**
     * @param mixed $content
     */
    public function withContent($content): self
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
