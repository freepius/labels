<?php

namespace App\Entity\Trait;

/**
 * Implement a simple string ID.
 */
trait IdTrait
{
    /**
     * An identifier.
     */
    protected string $id;

    /**
     * Set the identifier.
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     *  Get the identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }
}
