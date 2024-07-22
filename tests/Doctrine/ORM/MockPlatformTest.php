<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Doctrine\ORM;

use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Doctrine\ORM\MockPlatform;

class MockPlatformTest extends TestCase
{
    private MockPlatform $platform;

    protected function setUp(): void
    {
        $this->platform = new MockPlatform();
    }

    public function testBooleanDeclaration(): void
    {
        self::assertEquals('BOOLEAN', $this->platform->getBooleanTypeDeclarationSQL([]));
    }

    public function testClobDeclaration(): void
    {
        self::assertEquals('CLOB', $this->platform->getClobTypeDeclarationSQL([]));
    }

    public function testIntegerDeclaration(): void
    {
        self::assertEquals('INT', $this->platform->getIntegerTypeDeclarationSQL([]));
        self::assertEquals('INT UNSIGNED', $this->platform->getIntegerTypeDeclarationSQL(['unsigned' => true]));
        self::assertEquals('SERIAL', $this->platform->getIntegerTypeDeclarationSQL(['autoincrement' => true]));
        self::assertEquals('SERIAL', $this->platform->getIntegerTypeDeclarationSQL(['autoincrement' => true, 'unsigned' => true]));
    }

    public function testBigIntDeclaration(): void
    {
        self::assertEquals('BIGINT', $this->platform->getBigIntTypeDeclarationSQL([]));
        self::assertEquals('BIGINT UNSIGNED', $this->platform->getBigIntTypeDeclarationSQL(['unsigned' => true]));
        self::assertEquals('BIGSERIAL', $this->platform->getBigIntTypeDeclarationSQL(['autoincrement' => true]));
        self::assertEquals('BIGSERIAL', $this->platform->getBigIntTypeDeclarationSQL(['autoincrement' => true, 'unsigned' => true]));
    }

    public function testSmallIntDeclaration(): void
    {
        self::assertEquals('SMALLINT', $this->platform->getSmallIntTypeDeclarationSQL([]));
        self::assertEquals('SMALLINT UNSIGNED', $this->platform->getSmallIntTypeDeclarationSQL(['unsigned' => true]));
        self::assertEquals('SERIAL', $this->platform->getSmallIntTypeDeclarationSQL(['autoincrement' => true]));
        self::assertEquals('SERIAL', $this->platform->getSmallIntTypeDeclarationSQL(['autoincrement' => true, 'unsigned' => true]));
    }

    public function testVarcharDeclaration(): void
    {
        self::assertEquals('VARCHAR(255)', $this->platform->getVarcharTypeDeclarationSQL([]));
        self::assertEquals('VARCHAR(255)', $this->platform->getVarcharTypeDeclarationSQL(['length' => false]));
        self::assertEquals('VARCHAR(120)', $this->platform->getVarcharTypeDeclarationSQL(['length' => 120]));
        self::assertEquals('CHAR(255)', $this->platform->getVarcharTypeDeclarationSQL(['fixed' => true]));
        self::assertEquals('CHAR(120)', $this->platform->getVarcharTypeDeclarationSQL(['fixed' => true, 'length' => 120]));
        self::assertEquals('CLOB', $this->platform->getVarcharTypeDeclarationSQL(['length' => 65536]));
    }

    public function testBinaryDeclaration(): void
    {
        self::assertEquals('BLOB', $this->platform->getBlobTypeDeclarationSQL([]));
        self::assertEquals('VARBINARY(255)', $this->platform->getBinaryTypeDeclarationSQL([]));
        self::assertEquals('VARBINARY(255)', $this->platform->getBinaryTypeDeclarationSQL(['length' => false]));
        self::assertEquals('VARBINARY(120)', $this->platform->getBinaryTypeDeclarationSQL(['length' => 120]));
        self::assertEquals('BINARY(255)', $this->platform->getBinaryTypeDeclarationSQL(['fixed' => true]));
        self::assertEquals('BINARY(120)', $this->platform->getBinaryTypeDeclarationSQL(['fixed' => true, 'length' => 120]));
    }
}
