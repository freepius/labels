<?php

namespace App\Repository;

interface RepositoryInterface
{
    /**
     * Finds all objects in the repository.
     */
    public function findAll(): array;

    /**
     * Check if an object exists by its identifier.
     *
     * @param mixed  $id  The identifier.
     */
    public function exists(mixed $id): bool;

    /**
     * Finds an object by its identifier.
     *
     * @param mixed  $id  The identifier.
     *
     * @return object|null The object instance or NULL if it can not be found.
     */
    public function find(mixed $id): ?object;

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName(): string;
}
