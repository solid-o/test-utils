<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use RuntimeException;

use function implode;
use function sprintf;

final class Driver
{
    public const ANNOTATION = 'annotation';
    public const ATTRIBUTE = 'attribute';

    /** @param string[] $paths */
    public static function createDriver(string $driver, array $paths): MappingDriver
    {
        switch ($driver) {
            case self::ANNOTATION:
                return new AnnotationDriver(new AnnotationReader(), $paths);

            case self::ATTRIBUTE:
                return new AttributeDriver($paths);

            default:
                throw new RuntimeException(sprintf('"%s" driver is not supported by %s. Currently supported drivers are "%s"', $driver, __METHOD__, implode('", "', [self::ATTRIBUTE, self::ANNOTATION])));
        }
    }
}
