<?php

namespace App\Entity\Trait;

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
     * Merge the given data with the existing one.
     *
     * @todo Implement a smart recursive merge.
     */
    public function mergeData(array $data): self
    {
        $this->data += $data;

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
     * Get a data by its name.
     */
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Test if a data exists by its name.
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }
}
