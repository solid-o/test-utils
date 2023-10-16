<?php

declare(strict_types=1);

namespace Solido\TestUtils\Functional;

use PHPUnit\Framework\Assert;
use Solido\Common\AdapterFactory;
use Solido\TestUtils\Constraint\JsonResponse;
use Solido\TestUtils\Constraint\JsonResponseTrait;
use Solido\TestUtils\Constraint\ResponseHasHeaders;
use Solido\TestUtils\Constraint\ResponseHeaderSame;
use Solido\TestUtils\Constraint\ResponseLength;
use Solido\TestUtils\Constraint\ResponseStatusCode;
use Solido\TestUtils\Constraint\ResponseSubset;

use function array_keys;
use function json_decode;
use function range;

use const JSON_THROW_ON_ERROR;

class Response
{
    use JsonResponseTrait;

    private const STATUS_CODE_CLASS_INFORMATIONAL = 100;
    private const STATUS_CODE_CLASS_SUCCESS = 200;
    private const STATUS_CODE_CLASS_REDIRECTION = 300;
    private const STATUS_CODE_CLASS_CLIENT_ERROR = 400;
    private const STATUS_CODE_CLASS_SERVER_ERROR = 500;

    private const TYPE_JSON = 'json';

    /** @var callable(): object */
    private $performer;
    private bool $checked = false;
    private object $response;

    private int|null $statusCode;
    private int|null $statusCodeClass;

    /** @var array<string, string|null> */
    private array $headers;
    private string|null $type;

    /** @var string|array<(string|int), mixed>|null */
    private string|array|null $minimumSubset = null;
    private int|null $length;

    public function __construct(callable $performer)
    {
        $this->performer = $performer;
        $this->statusCode = null;
        $this->statusCodeClass = null;
        $this->headers = [];
        $this->type = null;
        $this->minimumSubset = null;
        $this->length = null;
    }

    public function __destruct()
    {
        $this->check();
    }

    public function shouldHaveStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        $this->statusCodeClass = null;

        return $this;
    }

    public function shouldHaveInformationalStatus(): self
    {
        $this->statusCodeClass = self::STATUS_CODE_CLASS_INFORMATIONAL;
        $this->statusCode = null;

        return $this;
    }

    public function shouldHaveSuccessStatus(): self
    {
        $this->statusCodeClass = self::STATUS_CODE_CLASS_SUCCESS;
        $this->statusCode = null;

        return $this;
    }

    public function shouldHaveRedirectionStatus(): self
    {
        $this->statusCodeClass = self::STATUS_CODE_CLASS_REDIRECTION;
        $this->statusCode = null;

        return $this;
    }

    public function shouldHaveClientErrorStatus(): self
    {
        $this->statusCodeClass = self::STATUS_CODE_CLASS_CLIENT_ERROR;
        $this->statusCode = null;

        return $this;
    }

    public function shouldHaveServerErrorStatus(): self
    {
        $this->statusCodeClass = self::STATUS_CODE_CLASS_SERVER_ERROR;
        $this->statusCode = null;

        return $this;
    }

    public function shouldHaveHeader(string $name, string|null $value = null): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function shouldBeJson(): self
    {
        $this->type = self::TYPE_JSON;

        return $this;
    }

    /** @param string | array<string|int, mixed> $content */
    public function shouldContainAtLeast(string|array $content): self
    {
        $this->minimumSubset = $content;

        return $this;
    }

    public function shouldHaveLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getResponse(): object
    {
        $this->check();

        return $this->response;
    }

    public function getProperty(string $propertyPath): mixed
    {
        $this->shouldBeJson();

        $adapterFactory = new AdapterFactory();
        $response = $adapterFactory->createResponseAdapter($this->getResponse());

        $content = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);

        return self::readProperty(self::getPropertyAccessor(), $content, $propertyPath);
    }

    public function check(): void
    {
        if ($this->checked) {
            return;
        }

        $this->checked = true;
        $this->response = $response = ($this->performer)();

        if ($this->statusCode !== null) {
            Assert::assertThat($response, new ResponseStatusCode($this->statusCode));
        }

        if ($this->statusCodeClass !== null) {
            Assert::assertThat($response, new ResponseStatusCode(...range($this->statusCodeClass, $this->statusCodeClass + 99)));
        }

        if (! empty($this->headers)) {
            Assert::assertThat($response, new ResponseHasHeaders(array_keys($this->headers)));
            foreach ($this->headers as $name => $value) {
                if ($value === null) {
                    continue;
                }

                Assert::assertThat($response, new ResponseHeaderSame($name, $value));
            }
        }

        if ($this->type === self::TYPE_JSON) {
            Assert::assertThat($response, new JsonResponse());
        }

        if ($this->minimumSubset !== null) {
            Assert::assertThat($response, new ResponseSubset($this->minimumSubset));
        }

        if ($this->length === null) {
            return;
        }

        Assert::assertThat($response, new ResponseLength($this->length));
    }
}
