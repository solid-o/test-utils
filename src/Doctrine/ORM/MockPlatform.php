<?php

declare(strict_types=1);

// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\DateIntervalUnit;
use Doctrine\DBAL\Platforms\Keywords\KeywordList;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\TableDiff;
use RuntimeException;

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

    protected function getVarcharTypeDeclarationSQLSnippet(mixed $length, mixed $fixed = false): string
    {
        $type = $fixed ? 'CHAR' : 'VARCHAR';
        if ($length === null) {
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

    public function getDateDiffExpression(string $date1, string $date2): string
    {
        return '(DATE(' . $date1 . ')-DATE(' . $date2 . '))';
    }

    protected function getDateArithmeticIntervalExpression(string $date, string $operator, string $interval, DateIntervalUnit $unit): string
    {
        $function = $operator === '+' ? 'DATE_ADD' : 'DATE_SUB';

        return $function . '(' . $date . ', INTERVAL ' . $interval . ' ' . $unit->value . ')';
    }

    /** @return list<string> */
    public function getAlterTableSQL(TableDiff $diff): array
    {
        return [];
    }

    public function getListViewsSQL(mixed $database): string
    {
        return '';
    }

    public function getSetTransactionIsolationSQL(mixed $level): string
    {
        return '';
    }

    /** @param array<string, mixed> $column */
    public function getDateTimeTypeDeclarationSQL(array $column): string
    {
        return 'DATETIME';
    }

    /** @param array<string, mixed> $column */
    public function getDateTypeDeclarationSQL(array $column): string
    {
        return 'DATE';
    }

    /** @param array<string, mixed> $column */
    public function getTimeTypeDeclarationSQL(array $column): string
    {
        return 'TIME';
    }

    protected function createReservedKeywordsList(): KeywordList
    {
        return new class extends KeywordList {
            /** @return list<string> */
            protected function getKeywords(): array
            {
                return [];
            }
        };
    }

    /** @return AbstractSchemaManager<AbstractPlatform> */
    public function createSchemaManager(Connection $connection): AbstractSchemaManager
    {
        throw new RuntimeException('Schema manager is not supported by the mock platform.');
    }
}
