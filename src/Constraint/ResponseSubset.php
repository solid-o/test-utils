<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use ArrayObject;
use Solido\Common\Exception\UnsupportedResponseObjectException;
use Solido\Common\ResponseAdapter\ResponseAdapterInterface;
use Traversable;

use function is_array;
use function is_string;
use function iterator_to_array;
use function json_decode;
use function json_last_error;
use function Safe\array_replace_recursive;
use function Safe\preg_match;
use function Safe\sprintf;
use function str_contains;

use const JSON_ERROR_NONE;
use const JSON_THROW_ON_ERROR;

final class ResponseSubset extends ResponseConstraint
{
    /** @var string|array<string|int, mixed>|object */
    private $subset;

    /**
     * @param string|array<string|int, mixed>|object $subset
     */
    public function __construct($subset)
    {
        $this->subset = $subset;
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException $e) {
            return false;
        }

        if (! $this->isJson($adapter)) {
            return is_string($this->subset) && str_contains($adapter->getContent(), $this->subset);
        }

        $other = json_decode($adapter->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->subset = $this->toArray($this->subset);

        $patched = array_replace_recursive($other, $this->subset);

        return $other === $patched;
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException $e) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        $otherContent = $this->isJson($adapter)
            ? json_decode($adapter->getContent(), true, 512, JSON_THROW_ON_ERROR)
            : $adapter->getContent();

        return sprintf(
            '%s contains subset %s. Actual response content is: %s',
            $this->exporter()->shortenedExport($other),
            $this->exporter()->export($this->subset),
            $this->exporter()->export($otherContent),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'contains subset %s',
            $this->exporter()->export($this->subset),
        );
    }

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

    /**
     * @param mixed $other
     *
     * @return array<string|int, mixed>
     */
    private function toArray($other): array
    {
        if (is_array($other)) {
            return $other;
        }

        if ($other instanceof ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof Traversable) {
            return iterator_to_array($other);
        }

        return (array) $other;
    }
}
