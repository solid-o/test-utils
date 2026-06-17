<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use RuntimeException;

use function class_exists;
use function implode;
use function sprintf;

final class Driver
{
    public const string ANNOTATION = 'annotation';
    public const string ATTRIBUTE = 'attribute';

    /** @param string[] $paths */
    public static function createDriver(string $driver, array $paths): MappingDriver
    {
        switch ($driver) {
            case self::ANNOTATION:
                $annotationReaderClass = 'Doctrine\\Common\\Annotations\\AnnotationReader';
                $annotationDriverClass = 'Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver';
                if (! class_exists($annotationReaderClass) || ! class_exists($annotationDriverClass)) {
                    throw new RuntimeException('Doctrine annotations driver is not available.');
                }

                $annotationDriver = new $annotationDriverClass(new $annotationReaderClass(), $paths);
                if (! $annotationDriver instanceof MappingDriver) {
                    throw new RuntimeException('Doctrine annotations driver is not a mapping driver.');
                }

                return $annotationDriver;

            case self::ATTRIBUTE:
                return new AttributeDriver($paths);

            default:
                throw new RuntimeException(sprintf('"%s" driver is not supported by %s. Currently supported drivers are "%s"', $driver, __METHOD__, implode('", "', [self::ATTRIBUTE, self::ANNOTATION])));
        }
    }
}
