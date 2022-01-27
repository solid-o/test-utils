<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Doctrine\ORM;

use Solido\TestUtils\Doctrine\ORM\DummyResult;
use PHPUnit\Framework\TestCase;

class DummyResultTest extends TestCase
{
    private DummyResult $result;

    protected function setUp(): void
    {
        $this->result = new DummyResult([
            ['col_1' => 'one', 'col_2' => 2],
            ['col_1' => 'two', 'col_2' => 42],
        ]);
    }

    public function testColumnCount(): void
    {
        self::assertEquals(2, $this->result->columnCount());
    }

    public function testFetchNumeric(): void
    {
        self::assertEquals(['one', 2], $this->result->fetchNumeric());
        self::assertEquals(['two', 42], $this->result->fetchNumeric());
        self::assertFalse($this->result->fetchNumeric());
    }

    public function testFetchAssociative(): void
    {
        self::assertEquals(['col_1' => 'one', 'col_2' => 2], $this->result->fetchAssociative());
        self::assertEquals(['col_1' => 'two', 'col_2' => 42], $this->result->fetchAssociative());
        self::assertFalse($this->result->fetchAssociative());
    }

    public function testFetchOne(): void
    {
        self::assertSame('one', $this->result->fetchOne());
        self::assertEquals('two', $this->result->fetchOne());
        self::assertFalse($this->result->fetchOne());
    }

    public function testFetchAllNumeric(): void
    {
        self::assertSame([
            ['one', 2],
            ['two', 42],
        ], $this->result->fetchAllNumeric());
    }

    public function testFetchAllAssociative(): void
    {
        self::assertSame([
            ['col_1' => 'one', 'col_2' => 2],
            ['col_1' => 'two', 'col_2' => 42],
        ], $this->result->fetchAllAssociative());
    }

    public function testFetchFirstColumn(): void
    {
        self::assertSame([
            'one',
            'two',
        ], $this->result->fetchFirstColumn());
    }
}
