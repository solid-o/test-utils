<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\DBAL\Driver\Result;

use function array_values;
use function count;
use function reset;

/**
 * @internal The class is internal to the caching layer implementation.
 */
final class DummyResult implements Result
{
    /** @var mixed[] */
    private array $data;
    private int $rowCount;
    private int $columnCount = 0;
    private int $num = 0;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data, ?int $rowCount = null)
    {
        $this->data = $data;
        $this->rowCount = $rowCount ?? count($data);
        if (count($data) === 0) {
            return;
        }

        $this->columnCount = count($data[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchNumeric()
    {
        $row = $this->fetch();

        if ($row === false) {
            return false;
        }

        return array_values($row);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAssociative()
    {
        return $this->fetch();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOne()
    {
        $row = $this->fetch();

        if ($row === false) {
            return false;
        }

        return reset($row);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllNumeric(): array
    {
        $rows = [];
        while (($row = $this->fetchNumeric()) !== false) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllAssociative(): array
    {
        $rows = [];
        while (($row = $this->fetchAssociative()) !== false) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchFirstColumn(): array
    {
        $rows = [];
        while (($row = $this->fetchOne()) !== false) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function rowCount(): int
    {
        return $this->rowCount;
    }

    public function columnCount(): int
    {
        return $this->columnCount;
    }

    public function free(): void
    {
        $this->data = [];
    }

    /**
     * @return false|mixed
     */
    private function fetch()
    {
        if (! isset($this->data[$this->num])) {
            return false;
        }

        return $this->data[$this->num++];
    }
}
