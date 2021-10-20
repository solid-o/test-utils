<?php

declare(strict_types=1);

// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;

use function Safe\sprintf;

class MockPlatform extends AbstractPlatform
{
    /**
     * {@inheritdoc}
     */
    public function getBooleanTypeDeclarationSQL(array $columnDef): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegerTypeDeclarationSQL(array $columnDef): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getBigIntTypeDeclarationSQL(array $columnDef): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $columnDef): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef): string
    {
        return '';
    }

    protected function initializeDoctrineTypeMappings(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getClobTypeDeclarationSQL(array $field): string
    {
        return 'CLOB';
    }

    /**
     * {@inheritdoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed): string
    {
        $type = $fixed ? 'CHAR' : 'VARCHAR';
        $length ??= 255;

        return sprintf('%s(%d)', $type, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlobTypeDeclarationSQL(array $field): string
    {
        return 'DUMMY_BINARY';
    }

    public function getName(): string
    {
        return 'dummy';
    }

    /**
     * {@inheritdoc}
     */
    protected function getBinaryTypeDeclarationSQLSnippet($length, $fixed): string
    {
        return sprintf('%s(%d)', $fixed ? 'DUMMY_BINARY' : 'DUMMY_VARBINARY', $length ?: 255);
    }

    public function getCurrentDatabaseExpression(): string
    {
        return 'DUMMY_DATABASE()';
    }
}
