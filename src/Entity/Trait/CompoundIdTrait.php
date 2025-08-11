<?php

namespace App\Entity\Trait;

/**
 * Implement a string ID that can be composed of multiple sub-IDs (according to a separator).
 */
trait CompoundIdTrait
{
    protected const COMPOUND_ID_SEPARATOR = '_';

    /**
     * An identifier which can be:
     * - simple (e.g. "foo")
     * - or compound (e.g. "foo_bar_baz").
     */
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
     * Get all the sub-IDs as a flat array.
     */
    public function getIds(): array
    {
        return explode(self::COMPOUND_ID_SEPARATOR, $this->getId());
    }

    /**
     * Get the nth sub-ID if any (null otherwise).
     */
    public function getIdSub(int $nth): ?string
    {
        return $this->getIds()[$nth] ?? null;
    }

    /**
     * Determine if the nth sub-ID exists.
     */
    public function hasIdSub(int $nth): bool
    {
        return isset($this->getIds()[$nth]);
    }

    /**
     * Extract a slice of the compound ID.
     * Return null if the offset is out of range.
     *
     * @see \array_slice() for the parameters
     */
    public function getIdSlice(int $offset, ?int $length = null): ?string
    {
        $slice = array_slice($this->getIds(), $offset, $length);

        return $slice ? implode(self::COMPOUND_ID_SEPARATOR, $slice) : null;
    }
}
