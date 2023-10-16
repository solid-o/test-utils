<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Solido\Common\AdapterFactory;
use Solido\Common\AdapterFactoryInterface;
use Solido\Common\Exception\UnsupportedResponseObjectException;
use Solido\Common\ResponseAdapter\ResponseAdapterInterface;

use function is_object;

abstract class ResponseConstraint extends Constraint
{
    private static AdapterFactoryInterface $adapterFactory;

    /**
     * Gets the response adapter for the given response object.
     */
    protected static function getResponseAdapter(mixed $response): ResponseAdapterInterface
    {
        if (! is_object($response)) {
            throw new UnsupportedResponseObjectException();
        }

        if (! isset(self::$adapterFactory)) {
            self::$adapterFactory = new AdapterFactory();
        }

        return self::$adapterFactory->createResponseAdapter($response);
    }
}
