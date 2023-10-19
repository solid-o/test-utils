<?php

declare(strict_types=1);

// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;

use function sprintf;

class MockPlatform extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    public function getBooleanTypeDeclarationSQL(array $column): string
    {
        return 'BOOLEAN';
    }

    /**
     * {@inheritDoc}
     */
    public function getIntegerTypeDeclarationSQL(array $column): string
    {
        if (! empty($column['autoincrement'])) {
            return 'SERIAL';
        }

        return 'INT' . $this->_getCommonIntegerTypeDeclarationSQL($column);
    }

    /**
     * {@inheritDoc}
     */
    public function getBigIntTypeDeclarationSQL(array $column): string
    {
        if (! empty($column['autoincrement'])) {
            return 'BIGSERIAL';
        }

        return 'BIGINT' . $this->_getCommonIntegerTypeDeclarationSQL($column);
    }

    /**
     * {@inheritDoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $column): string
    {
        if (! empty($column['autoincrement'])) {
            return 'SERIAL';
        }

        return 'SMALLINT' . $this->_getCommonIntegerTypeDeclarationSQL($column);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCommonIntegerTypeDeclarationSQL(array $column): string
    {
        return ! empty($column['unsigned']) ? ' UNSIGNED' : '';
    }

    protected function initializeDoctrineTypeMappings(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getClobTypeDeclarationSQL(array $column): string
    {
        return 'CLOB';
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed): string
    {
        $type = $fixed ? 'CHAR' : 'VARCHAR';
        if ($length === false) {
            $length = 255;
        }

        return sprintf('%s(%d)', $type, $length);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlobTypeDeclarationSQL(array $field): string
    {
        return 'BLOB';
    }

    public function getName(): string
    {
        return 'dummy';
    }

    /**
     * {@inheritDoc}
     */
    protected function getBinaryTypeDeclarationSQLSnippet($length, $fixed): string
    {
        return sprintf('%s(%d)', $fixed ? 'BINARY' : 'VARBINARY', $length ?: 255);
    }

    public function getCurrentDatabaseExpression(): string
    {
        return 'DATABASE()';
    }
}
