<?php

namespace App\Entity\Trait;

/**
 * Implement an ID which can be decomposed into multiple IDs (according to a separator).
 */
trait CompoundIdTrait
{
    protected const ID_SEPARATOR = '-';

    protected string $id;

    /**
     * Set the full ID.
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     *  Get the full ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get all the IDs as a flat array.
     */
    public function getIds(): array
    {
        return explode(self::ID_SEPARATOR, $this->getId());
    }

    /**
     * Get all the IDs but the last one.
     */
    public function getIdsAllButLast(): array
    {
        $ids = $this->getIds();

        array_pop($ids);

        return $ids;
    }

    /**
     * Get the last ID.
     */
    public function getIdLast(): string
    {
        return $this->getIds()[count($this->getIds()) - 1];
    }
}
