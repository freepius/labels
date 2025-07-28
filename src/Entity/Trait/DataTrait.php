<?php

namespace App\Entity\Trait;

use App\Util\ArrayUtil;

/**
 * Implements a way to store data in bulk, as an array.
 */
trait DataTrait
{
    protected array $data;

    /**
     * Set all data.
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get all data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Merge the given data with the existing one.
     *
     * @param array $data     The data to merge
     * @param bool $overwrite If true, the given data will overwrite the existing data.
     *                        If false, the existing data will be preserved and the new data will be appended.
     * @return self
     */
    public function mergeData(array $data, bool $overwrite = true): self
    {
        [$one, $two] = $overwrite
            ? [$this->data, $data]
            : [$data, $this->data];

        $this->data = ArrayUtil::mergeRecursiceDistinct($one, $two);

        return $this;
    }

    /**
     * Set a data by its name.
     */
    public function setDataValue(string $name, mixed $value): self
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Get a data by its name.
     */
    public function getDataValue(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    /**
     * Determines if a data exists by its name.
     */
    public function hasDataValue(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Get a data through a getter method or directly.
     */
    public function __get(string $name): mixed
    {
        return method_exists($this, $method = 'get' . ucfirst($name))
            ? $this->$method()
            : $this->getDataValue($name)
        ;
    }

    /**
     * Set a data through a setter method or directly.
     */
    public function __set(string $name, mixed $value): void
    {
        method_exists($this, $method = 'set' . ucfirst($name))
            ? $this->$method($value)
            : $this->setDataValue($name, $value)
        ;
    }

    /**
     * Test if a data exists through a 'has' method or directly.
     */
    public function __isset(string $name): bool
    {
        return method_exists($this, $method = 'has' . ucfirst($name))
            ? $this->$method()
            : $this->hasDataValue($name)
        ;
    }
}
