<?php

declare(strict_types=1);

// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\Keywords\KeywordList;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\TableDiff;

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
    protected function getVarcharTypeDeclarationSQLSnippet(mixed $length, mixed $fixed = false): string
    {
        $type = $fixed ? 'CHAR' : 'VARCHAR';
        if ($length === false || $length === null) {
            $length = 255;
        }

        return sprintf('%s(%d)', $type, $length);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlobTypeDeclarationSQL(array $column): string
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
    protected function getBinaryTypeDeclarationSQLSnippet(mixed $length, mixed $fixed = true): string
    {
        return sprintf('%s(%d)', $fixed ? 'BINARY' : 'VARBINARY', $length ?: 255);
    }

    public function getCurrentDatabaseExpression(): string
    {
        return 'DATABASE()';
    }

    public function getLocateExpression(mixed $string, mixed $substring, mixed $start = null): string
    {
        if ($start !== null) {
            $string = $this->getSubstringExpression($string, $start);

            return 'CASE WHEN (POSITION(' . $substring . ' IN ' . $string . ') = 0) THEN 0'
                . ' ELSE (POSITION(' . $substring . ' IN ' . $string . ') + ' . $start . ' - 1) END';
        }

        return sprintf('POSITION(%s IN %s)', $substring, $string);
    }

    public function getDateDiffExpression(mixed $date1, mixed $date2): string
    {
        return '(DATE(' . $date1 . ')-DATE(' . $date2 . '))';
    }

    protected function getDateArithmeticIntervalExpression(mixed $date, mixed $operator, mixed $interval, mixed $unit): string
    {
        $function = $operator === '+' ? 'DATE_ADD' : 'DATE_SUB';

        return $function . '(' . $date . ', INTERVAL ' . $interval . ' ' . $unit->value . ')';
    }

    public function getAlterTableSQL(TableDiff $diff): array
    {
        // TODO: Implement getAlterTableSQL() method.
    }

    public function getListViewsSQL(mixed $database): string
    {
        // TODO: Implement getListViewsSQL() method.
    }

    public function getSetTransactionIsolationSQL(mixed $level): string
    {
        // TODO: Implement getSetTransactionIsolationSQL() method.
    }

    public function getDateTimeTypeDeclarationSQL(array $column): string
    {
        // TODO: Implement getDateTimeTypeDeclarationSQL() method.
    }

    public function getDateTypeDeclarationSQL(array $column): string
    {
        // TODO: Implement getDateTypeDeclarationSQL() method.
    }

    public function getTimeTypeDeclarationSQL(array $column): string
    {
        // TODO: Implement getTimeTypeDeclarationSQL() method.
    }

    protected function createReservedKeywordsList(): KeywordList
    {
        // TODO: Implement createReservedKeywordsList() method.
    }

    public function createSchemaManager(Connection $connection): AbstractSchemaManager
    {
        // TODO: Implement createSchemaManager() method.
    }
}
